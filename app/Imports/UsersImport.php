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
    private int    $imported = 0;
    private int    $skipped  = 0;

    public function __construct(string $importType)
    {
        $this->importType = $importType;
    }

    public function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, false, true);

        if (empty($rows)) {
            $this->importErrors[] = 'File tidak memiliki data.';
            return $this->result();
        }

        // ── Detect header row ──────────────────────────────────────────────
        $headerRowIndex = null;
        $colMap         = [];

        foreach ($rows as $rowIndex => $row) {
            $normalized = array_map(fn ($v) => strtolower(trim((string) $v)), $row);
            $hasName    = in_array('name', $normalized) || in_array('nama', $normalized);
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
            foreach (array_map(fn ($v) => strtolower(trim((string) $v)), $rows[$firstKey]) as $col => $val) {
                $colMap[$val] = $col;
            }
        }

        $isBk    = $this->importType === 'bk';
        $nameCol = $colMap['name'] ?? $colMap['nama'] ?? null;
        $emailCol = $colMap['email'] ?? null;
        $idCol   = $isBk ? ($colMap['nip'] ?? null) : ($colMap['nis'] ?? null);

        if (!$nameCol) {
            $this->importErrors[] = 'Kolom "name" / "nama" tidak ditemukan di header.';
            return $this->result();
        }
        if (!$idCol) {
            $this->importErrors[] = 'Kolom "' . ($isBk ? 'nip' : 'nis') . '" tidak ditemukan di header.';
            return $this->result();
        }

        $kelasCol   = !$isBk ? ($colMap['kelas']   ?? null) : null;
        $jurusanCol = !$isBk ? ($colMap['jurusan']  ?? null) : null;

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

            $model::create([
                'name'                 => $name,
                'email'                => $cleanEmail,
                'login_id'             => $loginId,
                'password'             => Hash::make($loginId),
                'must_change_password' => true,
                ...($this->resolveClassroom($isBk, $kelasVal, $jurusanVal)),
            ]);

            $this->importedRecords[] = ['name' => $name, 'id_label' => $idLabel, 'id' => $loginId];
            $this->imported++;
        }

        return $this->result();
    }

    private function resolveClassroom(bool $isBk, string $kelas, string $jurusan): array
    {
        if ($isBk || !$kelas || !$jurusan) return [];

        $kelasRecord = Kelas::where('kelas', $kelas)->where('jurusan', $jurusan)->first();
        if (!$kelasRecord) return [];

        $classroom = Classroom::where('class_id', $kelasRecord->id)->first();
        if (!$classroom) return [];

        return [
            'classroom_id' => $classroom->id,
            'bk_id'        => $kelasRecord->bk_id,
        ];
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
