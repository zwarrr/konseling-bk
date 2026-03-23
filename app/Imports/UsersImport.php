<?php

namespace App\Imports;

use App\Models\BkAccount;
use App\Models\Classroom;
use App\Models\Kelas;
use App\Models\SiswaAccount;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UsersImport
{
    private string $importType; // 'bk' | 'siswa'
    private array  $importErrors    = [];
    private array  $importedRecords = [];
    private array  $touchedClassIds = [];
    private int    $imported = 0;
    private int    $skipped  = 0;

    public function __construct(string $importType)
    {
        $this->importType = $importType;
    }

    public function import(string $filePath): array
    {
        $normalizeHeader = function ($value): string {
            $v = strtolower(trim((string) $value));
            // Normalize multiline/annotated headers, e.g. "Jenis Kelamin (P/L)"
            $v = preg_replace('/\s+/u', ' ', $v);
            $v = str_replace(['(', ')', '.', '-', '_'], ' ', $v);
            $v = preg_replace('/\s+/u', ' ', $v);
            return trim($v);
        };

        $findCol = function (array $map, array $aliases): ?string {
            $normalizedAliases = array_map(function ($a) {
                $a = strtolower(trim($a));
                $a = preg_replace('/\s+/u', ' ', $a);
                return $a;
            }, $aliases);

            foreach ($normalizedAliases as $alias) {
                if (isset($map[$alias])) return $map[$alias];
            }

            foreach ($map as $key => $col) {
                foreach ($normalizedAliases as $alias) {
                    if ($key === $alias || str_contains($key, $alias) || str_contains($alias, $key)) {
                        return $col;
                    }
                }
            }

            return null;
        };

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, false, true);

        if (empty($rows)) {
            $this->importErrors[] = ['reason' => 'File tidak memiliki data.'];
            return $this->result();
        }

        // ── Detect header row ──────────────────────────────────────────────
        $headerRowIndex = null;
        $colMap         = [];

        foreach ($rows as $rowIndex => $row) {
            $normalized = array_map($normalizeHeader, $row);
            $hasName    = in_array('name', $normalized) || in_array('nama', $normalized) || in_array('nama lengkap', $normalized);
            $hasId      = in_array('nip', $normalized)  || in_array('nis', $normalized);

            if ($hasName && $hasId) {
                $headerRowIndex = $rowIndex;
                foreach ($normalized as $col => $val) {
                    $colMap[$val] = $col;
                }
                break;
            }
        }

        if ($headerRowIndex === null) {
            $firstKey       = array_key_first($rows);
            $headerRowIndex = $firstKey;
            foreach (array_map($normalizeHeader, $rows[$firstKey]) as $col => $val) {
                $colMap[$val] = $col;
            }
        }

        $isBk     = $this->importType === 'bk';
        $nameCol  = $findCol($colMap, ['name', 'nama', 'nama lengkap']);
        $emailCol = $findCol($colMap, ['email', 'alamat email', 'e mail']);
        $idCol    = $isBk
            ? $findCol($colMap, ['nip', 'nip bk'])
            : $findCol($colMap, ['nis', 'nis siswa']);

        if (!$nameCol) {
            $this->importErrors[] = ['reason' => 'Kolom "name" / "nama" tidak ditemukan di header.'];
            return $this->result();
        }
        if (!$idCol) {
            $this->importErrors[] = ['reason' => 'Kolom "' . ($isBk ? 'nip' : 'nis') . '" tidak ditemukan di header.'];
            return $this->result();
        }

        $kelasCol   = !$isBk ? $findCol($colMap, ['kelas']) : null;
        $jurusanCol = !$isBk ? $findCol($colMap, ['jurusan']) : null;
        $genderCol  = !$isBk
            ? $findCol($colMap, ['jenis kelamin', 'jk', 'gender', 'p/l', 'pl'])
            : null;

        if (!$isBk && !$kelasCol) {
            $this->importErrors[] = ['reason' => 'Kolom "kelas" wajib ada untuk import siswa.'];
            return $this->result();
        }

        if (!$isBk && !$genderCol) {
            $this->importErrors[] = ['reason' => 'Kolom "jenis kelamin" (P/L) wajib ada untuk import siswa.'];
            return $this->result();
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $isBk ? BkAccount::class : SiswaAccount::class;

        // ── Iterate data rows ──────────────────────────────────────────────
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === $headerRowIndex) continue;

            $name     = trim((string) ($row[$nameCol] ?? ''));
            $rawIdVal = $row[$idCol] ?? '';
            if (is_int($rawIdVal) || is_float($rawIdVal)) {
                $rawId = rtrim(number_format((float) $rawIdVal, 0, '.', ''), '.');
            } else {
                $rawId = ltrim(trim((string) $rawIdVal), "'");
            }
            $email      = $emailCol   ? trim((string) ($row[$emailCol]   ?? '')) : '';
            $kelasVal   = $kelasCol   ? trim((string) ($row[$kelasCol]   ?? '')) : '';
            $jurusanVal = $jurusanCol ? trim((string) ($row[$jurusanCol] ?? '')) : '';
            $genderVal  = $genderCol  ? trim((string) ($row[$genderCol]  ?? '')) : '';

            if (!$name && !$rawId) continue;

            $idLabel = $isBk ? 'NIP' : 'NIS';

            if (!$name) {
                $this->importErrors[] = ['name' => null, 'id_label' => $idLabel, 'id' => null, 'reason' => "Baris {$rowIndex}: nama kosong."];
                $this->skipped++;
                continue;
            }

            $loginId = preg_replace('/\D+/', '', $rawId);

            if (!$loginId) {
                $this->importErrors[] = ['name' => $name, 'id_label' => $idLabel, 'id' => null, 'reason' => 'ID kosong atau bukan angka.'];
                $this->skipped++;
                continue;
            }

            if ($isBk && (strlen($loginId) < 9 || strlen($loginId) > 18)) {
                $this->importErrors[] = ['name' => $name, 'id_label' => $idLabel, 'id' => $loginId, 'reason' => 'tidak valid (harus 9–18 digit).'];
                $this->skipped++;
                continue;
            }

            if (!$isBk && strlen($loginId) > 10) {
                $this->importErrors[] = ['name' => $name, 'id_label' => $idLabel, 'id' => $loginId, 'reason' => 'maksimal 10 digit.'];
                $this->skipped++;
                continue;
            }

            if ($model::where('login_id', $loginId)->exists()) {
                $this->importErrors[] = ['name' => $name, 'id_label' => $idLabel, 'id' => $loginId, 'reason' => 'sudah terdaftar.'];
                $this->skipped++;
                continue;
            }

            $jenisKelamin = null;
            if (!$isBk) {
                $jenisKelamin = $this->normalizeJenisKelamin($genderVal);
                if (!$jenisKelamin) {
                    $this->importErrors[] = ['name' => $name, 'id_label' => $idLabel, 'id' => $loginId, 'reason' => 'jenis kelamin wajib P/L.'];
                    $this->skipped++;
                    continue;
                }
            }

            $cleanEmail = null;
            if ($email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL) && str_ends_with(strtolower($email), '@gmail.com')) {
                    if ($model::where('email', $email)->doesntExist()) {
                        $cleanEmail = $email;
                    } else {
                        $this->importErrors[] = ['name' => $name, 'id_label' => $idLabel, 'id' => $loginId, 'reason' => 'email sudah dipakai, diabaikan.'];
                    }
                } else {
                    $this->importErrors[] = ['name' => $name, 'id_label' => $idLabel, 'id' => $loginId, 'reason' => 'email tidak valid (@gmail.com), diabaikan.'];
                }
            }

            $resolvedClass = $this->resolveClassroom($isBk, $kelasVal, $jurusanVal);
            if (!$isBk && $kelasVal && empty($resolvedClass)) {
                $this->importErrors[] = [
                    'name' => $name,
                    'id_label' => $idLabel,
                    'id' => $loginId,
                    'reason' => 'format kelas tidak valid. Gunakan format seperti 10PPLG, 11AKL, atau isi jurusan di kolom terpisah.',
                ];
                $this->skipped++;
                continue;
            }
            $resolvedClassId = $resolvedClass['_class_id'] ?? null;
            unset($resolvedClass['_class_id']);

            $model::create([
                'name'                 => $name,
                'email'                => $cleanEmail,
                'login_id'             => $loginId,
                'password'             => Hash::make($loginId),
                'must_change_password' => true,
                ...(!$isBk ? ['jenis_kelamin' => $jenisKelamin] : []),
                ...$resolvedClass,
            ]);

            if ($resolvedClassId) {
                $this->touchedClassIds[(int) $resolvedClassId] = true;
            }

            $this->importedRecords[] = ['name' => $name, 'id_label' => $idLabel, 'id' => $loginId];
            $this->imported++;
        }

        $this->syncClassTotals();

        return $this->result();
    }

    private function resolveClassroom(bool $isBk, string $kelas, string $jurusan): array
    {
        if ($isBk || !$kelas) return [];

        [$kelasNormalized, $jurusanFromKelas] = $this->splitKelasDanJurusan($kelas);
        $jurusanNormalized = strtoupper(trim($jurusan));
        if (!$jurusanNormalized) {
            $jurusanNormalized = $jurusanFromKelas;
        }

        if (!$kelasNormalized || !$jurusanNormalized) return [];

        $kelasRecord = Kelas::firstOrCreate(
            ['kelas' => $kelasNormalized, 'jurusan' => $jurusanNormalized],
            ['jumlah_siswa_i' => 0]
        );

        $bkAccountId = null;
        if ($kelasRecord->bk_id) {
            $bkAccountId = BkAccount::where('id', $kelasRecord->bk_id)->value('account_id');
        }

        $classroom = Classroom::where('class_id', $kelasRecord->id)->first();
        if (!$classroom) {
            $classroom = Classroom::create([
                'name'          => trim($kelasRecord->kelas . ' ' . $kelasRecord->jurusan),
                'description'   => 'Kelas ' . trim($kelasRecord->kelas . ' ' . $kelasRecord->jurusan),
                'class_id'      => $kelasRecord->id,
                'bk_account_id' => $bkAccountId,
            ]);
        } elseif ($bkAccountId && $classroom->bk_account_id !== $bkAccountId) {
            $classroom->update(['bk_account_id' => $bkAccountId]);
        }

        return [
            'classroom_id' => $classroom->id,
            'bk_id'        => $kelasRecord->bk_id,
            '_class_id'    => $kelasRecord->id,
        ];
    }

    private function normalizeJenisKelamin(string $value): ?string
    {
        $v = strtolower(trim($value));
        if ($v === '') return null;

        if (in_array($v, ['l', 'laki', 'laki-laki', 'lakilaki', 'male', 'm'], true)) {
            return 'L';
        }
        if (in_array($v, ['p', 'pr', 'perempuan', 'female', 'f'], true)) {
            return 'P';
        }

        return null;
    }

    private function splitKelasDanJurusan(string $kelasRaw): array
    {
        $value = strtoupper(preg_replace('/\s+/', '', trim($kelasRaw)));
        if ($value === '') return ['', ''];

        // Preferred import format: one column like 10PPLG / 11AKL / 12RPL1.
        if (preg_match('/^(10|11|12)([A-Z][A-Z0-9\/-]*)$/', $value, $m)) {
            return [$m[1], strtoupper($m[2])];
        }

        // Backward compatibility for roman numerals (X/XI/XII) in legacy sheets.
        if (preg_match('/^(XII|XI|X)([A-Z][A-Z0-9\/-]*)$/', $value, $m)) {
            $kelas = match ($m[1]) {
                'X' => '10',
                'XI' => '11',
                'XII' => '12',
                default => '',
            };
            return [$kelas, strtoupper($m[2])];
        }

        return [strtoupper($value), ''];
    }

    private function syncClassTotals(): void
    {
        if (empty($this->touchedClassIds)) return;

        foreach (array_keys($this->touchedClassIds) as $classId) {
            $count = SiswaAccount::whereHas('classroom', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })->count();

            Kelas::whereKey($classId)->update(['jumlah_siswa_i' => $count]);
        }
    }

    private function result(): array
    {
        return [
            'imported'         => $this->imported,
            'skipped'          => $this->skipped,
            'errors'           => $this->importErrors,
            'imported_records' => $this->importedRecords,
        ];
    }
}
