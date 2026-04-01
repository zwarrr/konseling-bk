<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BkAccount;
use App\Models\Classroom;
use App\Models\Kelas;
use App\Services\KelasSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminKelasController extends Controller
{
    /** Roman numeral + number only validation pattern */
    private const KELAS_REGEX = '/^[IVXLCDMivxlcdm0-9]+$/';

    public function index(): View
    {
        $dataKelas = Kelas::with('bk')->orderBy('kelas')->orderBy('jurusan')->get();
        $allBk     = BkAccount::orderBy('name')->get(['id', 'name', 'account_id']);
        return view('admin.sections.kelola_data.data_kelas', compact('dataKelas', 'allBk'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kelas'          => ['required', 'string', 'max:20', 'regex:' . self::KELAS_REGEX],
            'jurusan'        => 'required|string|max:100',
            'jumlah_siswa_i' => 'required|integer|min:0|max:9999',
            'bk_id'          => 'nullable|integer|exists:bk_account,id',
        ], [
            'kelas.regex' => 'Kelas hanya boleh mengandung angka atau huruf romawi (misalnya XII, X, 10).',
        ]);

        $data['kelas'] = $this->normalizeKelasToRoman($data['kelas']);

        // Unique check
        if (Kelas::where('kelas', $data['kelas'])->where('jurusan', $data['jurusan'])->exists()) {
            return response()->json(['errors' => ['kelas' => ['Kombinasi kelas dan jurusan sudah ada.']]], 422);
        }

        $mk = Kelas::create($data);

        // Sync kelas->classroom->bk and related student assignments
        KelasSync::fromKelas($mk->fresh());

        return response()->json(['class' => $mk], 201);
    }

    public function update(Request $request, Kelas $masterKela): JsonResponse
    {
        $data = $request->validate([
            'kelas'          => ['required', 'string', 'max:20', 'regex:' . self::KELAS_REGEX],
            'jurusan'        => 'required|string|max:100',
            'jumlah_siswa_i' => 'required|integer|min:0|max:9999',
            'bk_id'          => 'nullable|integer|exists:bk_account,id',
        ], [
            'kelas.regex' => 'Kelas hanya boleh mengandung angka atau huruf romawi (misalnya XII, X, 10).',
        ]);

        $data['kelas'] = $this->normalizeKelasToRoman($data['kelas']);

        // Unique check (exclude self)
        if (Kelas::where('kelas', $data['kelas'])
            ->where('jurusan', $data['jurusan'])
            ->where('id', '!=', $masterKela->id)
            ->exists()) {
            return response()->json(['errors' => ['kelas' => ['Kombinasi kelas dan jurusan sudah ada.']]], 422);
        }

        $masterKela->update($data);

        // Cascade bk assignment to classrooms + students
        KelasSync::fromKelas($masterKela->fresh());

        return response()->json(['class' => $masterKela->fresh()]);
    }

    public function destroy(Kelas $masterKela): JsonResponse
    {
        $masterKela->delete();
        return response()->json(['ok' => true]);
    }

    /** POST /kelola/data-kelas/import — Import kelas dari Excel. */
    public function importKelas(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,ods|max:5120',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, .csv, atau .ods.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        // Detect header row
        $headerIdx = null;
        $colMap    = [];
        foreach ($rows as $ri => $row) {
            $norm = array_map(fn ($v) => strtolower(trim((string) $v)), $row);
            if (in_array('kelas', $norm) || in_array('jurusan', $norm)) {
                $headerIdx = $ri;
                foreach ($norm as $col => $val) { $colMap[$val] = $col; }
                break;
            }
        }
        if ($headerIdx === null) {
            $headerIdx = array_key_first($rows);
            foreach (array_map(fn ($v) => strtolower(trim((string) $v)), $rows[$headerIdx]) as $col => $val) {
                $colMap[$val] = $col;
            }
        }

        // Preload BK map (name lowercase → account)
        $bkMap = BkAccount::all()->keyBy(fn ($b) => strtolower(trim($b->name)));

        $kelasCol   = $colMap['kelas']          ?? null;
        $jurusanCol = $colMap['jurusan']         ?? null;
        $jumlahCol  = $colMap['jumlah_siswa_i']
            ?? $colMap['jumlah siswa/i']
            ?? $colMap['jumlah siswa i']
            ?? $colMap['jumlah']
            ?? null;
        $bkCol      = $colMap['bk']             ?? $colMap['pembimbing'] ?? null;

        if (!$kelasCol || !$jurusanCol) {
            return back()->with('error', 'Kolom "kelas" dan "jurusan" wajib ada di header.');
        }

        $imported = 0;
        $skipped  = 0;
        $notes    = [];

        foreach ($rows as $ri => $row) {
            if ($ri === $headerIdx) continue;
            $kelas   = $this->normalizeKelasToRoman((string) ($row[$kelasCol] ?? ''));
            $jurusan = trim((string) ($row[$jurusanCol] ?? ''));
            if (!$kelas || !$jurusan) continue;

            $jumlah = $jumlahCol ? (int) ($row[$jumlahCol] ?? 0) : 0;
            $bkId   = null;
            if ($bkCol) {
                $bkName = strtolower(trim((string) ($row[$bkCol] ?? '')));
                if ($bkName) {
                    $matched = $bkMap->first(fn ($b, $k) => str_contains($k, $bkName) || str_contains($bkName, $k));
                    $bkId    = $matched?->id;
                    if (!$bkId && $bkName) {
                        $notes[] = "⚠ BK \"{$row[$bkCol]}\" tidak ditemukan untuk {$kelas} {$jurusan}.";
                    }
                }
            }

            // Validate kelas format
            if (!preg_match('/^[IVXLCDMivxlcdm0-9]+$/', $kelas)) {
                $notes[] = "✗ {$kelas} {$jurusan}: format kelas tidak valid (hanya angka/romawi).";
                $skipped++;
                continue;
            }

            $exists = Kelas::where('kelas', $kelas)->where('jurusan', $jurusan)->first();
            if ($exists) {
                $exists->update(['jumlah_siswa_i' => $jumlah, 'bk_id' => $bkId ?? $exists->bk_id]);
                KelasSync::fromKelas($exists->fresh());
                $notes[] = "↻ {$kelas} {$jurusan}: diperbarui.";
                $imported++;
            } else {
                $mk = Kelas::create(['kelas' => $kelas, 'jurusan' => $jurusan, 'jumlah_siswa_i' => $jumlah, 'bk_id' => $bkId]);
                KelasSync::fromKelas($mk);
                $notes[] = "✓ {$kelas} {$jurusan}: ditambahkan.";
                $imported++;
            }
        }

        $msg = "Berhasil memproses {$imported} data kelas.";
        if ($skipped) $msg .= " {$skipped} baris dilewati.";
        return redirect()->route('admin.kelas.index')
            ->with('success', $msg)
            ->with('flash_notes', $notes);
    }

    private function normalizeKelasToRoman(string $kelas): string
    {
        $k = strtoupper(trim($kelas));
        return match ($k) {
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII',
            default => $k,
        };
    }

    public function downloadKelasImportTemplate()
    {
        $candidates = ['contoh_import_data_kelas.xlsx', 'contoh_import_data_kelas.csv'];
        $found = collect($candidates)
            ->map(fn (string $name) => [
                'name' => $name,
                'path' => public_path('templates/import/' . $name),
            ])
            ->first(fn (array $f) => is_file($f['path']));

        if (!$found) {
            abort(404, 'Template tidak ditemukan.');
        }

        return response()->download($found['path'], $found['name']);
    }

    /** API: Return all data kelas as JSON (used by BK dropdown). */
    public function listJson(): JsonResponse
    {
        $items = Kelas::orderBy('kelas')->orderBy('jurusan')->get();

        // IDs that already have a classroom assigned (any BK)
        $takenIds = \App\Models\Classroom::whereNotNull('class_id')
            ->pluck('class_id')
            ->unique()
            ->values();

        return response()->json([
            'data'      => $items,
            'taken_ids' => $takenIds,
        ]);
    }
}
