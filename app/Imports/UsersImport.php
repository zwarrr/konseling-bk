<?php

namespace App\Imports;

use App\Models\BkAccount;
use App\Models\Classroom;
use App\Models\Kelas;
use App\Models\SiswaAccount;
use App\Services\KelasSync;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UsersImport
{
    private string $importType; // 'bk' | 'siswa'
    private array  $importErrors    = [];
    private array  $importedRecords = [];
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
        $emailCol = $findCol($colMap, ['email', 'alamat email', 'email address', 'e mail']);
        $idCol    = $isBk
            ? $findCol($colMap, ['nip', 'nip bk', 'nis', 'id'])
            : $findCol($colMap, ['nis', 'nis siswa', 'nip', 'id']);

        if (!$nameCol) {
            $this->importErrors[] = ['reason' => 'Kolom "name" / "nama" tidak ditemukan di header.'];
            return $this->result();
        }
        if (!$idCol) {
            $this->importErrors[] = ['reason' => 'Kolom "' . ($isBk ? 'nip' : 'nis') . '" tidak ditemukan di header.'];
            return $this->result();
        }

        $kelasCol   = $findCol($colMap, ['kelas']);
        $jurusanCol = $findCol($colMap, ['jurusan']);
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
                if (preg_match('/^[0-9]+(?:\.[0-9]+)?e\+[0-9]+$/i', $rawId)) {
                    $rawId = rtrim(number_format((float) $rawId, 0, '.', ''), '.');
                }
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

            $existing = $model::where('login_id', $loginId)->first();
            if ($existing) {
                if ($isBk && $existing instanceof BkAccount && $kelasVal) {
                    $this->syncBkAssignments($existing, $kelasVal, $jurusanVal);
                    $this->importedRecords[] = [
                        'name' => $existing->name ?: $name,
                        'id_label' => $idLabel,
                        'id' => $loginId,
                    ];
                    $this->imported++;
                    continue;
                }

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

            $created = $model::create([
                'name'                 => $name,
                'email'                => $cleanEmail,
                'login_id'             => $loginId,
                'password'             => Hash::make($loginId),
                'must_change_password' => true,
                ...(!$isBk ? ['jenis_kelamin' => $jenisKelamin] : []),
                ...$resolvedClass,
            ]);

            if ($isBk && $created instanceof BkAccount && $kelasVal) {
                $this->syncBkAssignments($created, $kelasVal, $jurusanVal);
            }

            $this->importedRecords[] = ['name' => $name, 'id_label' => $idLabel, 'id' => $loginId];
            $this->imported++;
        }

        return $this->result();
    }

    private function resolveClassroom(bool $isBk, string $kelas, string $jurusan): array
    {
        if ($isBk || !$kelas) return [];

        [$kelasNormalized, $jurusanFromKelas] = $this->splitKelasDanJurusan($kelas);
        $jurusanNormalized = $this->normalizeJurusan($jurusan);
        if (!$jurusanNormalized) {
            $jurusanNormalized = $this->normalizeJurusan($jurusanFromKelas);
        }

        if (!$kelasNormalized || !$jurusanNormalized) return [];

        $kelasRecord = $this->findOrCreateKelasRecord($kelasNormalized, $jurusanNormalized);

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

    private function syncBkAssignments(BkAccount $bk, string $kelasValue, string $jurusanValue): void
    {
        $chunks = preg_split('/[,;]+/', $kelasValue) ?: [];
        foreach ($chunks as $chunk) {
            $raw = trim($chunk);
            if ($raw === '') continue;

            [$kelasNormalized, $jurusanFromKelas] = $this->splitKelasDanJurusan($raw);
            $jurusanNormalized = $this->normalizeJurusan($jurusanValue ?: $jurusanFromKelas);
            if (!$kelasNormalized || !$jurusanNormalized) {
                continue;
            }

            $kelasRecord = $this->findOrCreateKelasRecord($kelasNormalized, $jurusanNormalized);
            if (!$kelasRecord->bk_id || (int) $kelasRecord->bk_id !== (int) $bk->id) {
                $kelasRecord->update(['bk_id' => $bk->id]);
            }

            KelasSync::fromKelas($kelasRecord->fresh());
        }
    }

    private function findOrCreateKelasRecord(string $kelas, string $jurusan): Kelas
    {
        $canonicalKelas = $this->toRomanKelas($kelas);
        $kelasCandidates = collect([$canonicalKelas, $this->toNumericKelas($canonicalKelas)])
            ->filter()
            ->unique()
            ->values();

        $matches = Kelas::query()
            ->whereIn('kelas', $kelasCandidates->all())
            ->where('jurusan', $jurusan)
            ->orderBy('id')
            ->get();

        if ($matches->isNotEmpty()) {
            $primary = $matches->firstWhere('kelas', $canonicalKelas) ?? $matches->first();
            $secondary = $matches->where('id', '!=', $primary->id);

            if ($primary->kelas !== $canonicalKelas) {
                $primary->kelas = $canonicalKelas;
            }

            foreach ($secondary as $dup) {
                Classroom::where('class_id', $dup->id)->update(['class_id' => $primary->id]);

                if (!$primary->bk_id && $dup->bk_id) {
                    $primary->bk_id = $dup->bk_id;
                }
                if ($primary->jumlah_siswa_i === null && $dup->jumlah_siswa_i !== null) {
                    $primary->jumlah_siswa_i = $dup->jumlah_siswa_i;
                }

                $dup->delete();
            }

            if ($primary->isDirty()) {
                $primary->save();
            }

            return $primary->fresh();
        }

        return Kelas::create([
            'kelas' => $canonicalKelas,
            'jurusan' => $jurusan,
            'jumlah_siswa_i' => null,
        ]);
    }

    private function normalizeJurusan(string $value): string
    {
        $jurusan = strtoupper(trim(preg_replace('/\s+/', '', $value)));
        if ($jurusan === '') return '';

        if (preg_match('/^([A-Z\/-]+)\d+$/', $jurusan, $m)) {
            return $m[1];
        }

        return $jurusan;
    }

    private function toNumericKelas(string $kelas): string
    {
        $k = strtoupper(trim($kelas));
        return match ($k) {
            'X' => '10',
            'XI' => '11',
            'XII' => '12',
            default => $k,
        };
    }

    private function toRomanKelas(string $kelas): string
    {
        $k = strtoupper(trim($kelas));
        return match ($k) {
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII',
            default => $k,
        };
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
        $clean = strtoupper(trim(preg_replace('/\s+/', ' ', $kelasRaw)));
        if ($clean === '') return ['', ''];

        if (preg_match('/^(10|11|12|X|XI|XII)\s+([A-Z][A-Z0-9\/-]*)(?:\s*\d+)?$/', $clean, $m)) {
            $kelas = $this->toRomanKelas($m[1]);
            $jurusan = $this->normalizeJurusan($m[2]);
            return [$kelas, $jurusan];
        }

        $value = strtoupper(preg_replace('/\s+/', '', $clean));

        // Preferred import format: one column like 10PPLG / 11AKL / 12RPL1.
        if (preg_match('/^(10|11|12)([A-Z][A-Z0-9\/-]*)$/', $value, $m)) {
            return [$this->toRomanKelas($m[1]), $this->normalizeJurusan($m[2])];
        }

        // Backward compatibility for roman numerals (X/XI/XII) in legacy sheets.
        if (preg_match('/^(XII|XI|X)([A-Z][A-Z0-9\/-]*)$/', $value, $m)) {
            $kelas = match ($m[1]) {
                'X' => '10',
                'XI' => '11',
                'XII' => '12',
                default => '',
            };
            return [$this->toRomanKelas($kelas), $this->normalizeJurusan($m[2])];
        }

        return [strtoupper($value), ''];
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
