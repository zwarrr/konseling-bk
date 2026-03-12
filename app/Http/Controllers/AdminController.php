<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\AdminAccount;
use App\Models\BkAccount;
use App\Models\Classroom;
use App\Models\Kelas;
use App\Models\SiswaAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ─── Dashboard ───────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $totalBK              = BkAccount::count();
        $totalSiswa           = SiswaAccount::count();
        $totalUsers           = $totalBK + $totalSiswa;
        $totalDataKelas       = Kelas::count();
        $totalClassroom       = Classroom::count();
        $totalSiswaBelumKelas = SiswaAccount::whereNull('classroom_id')->count();

        return view('admin.sections.dashboard', compact(
            'totalUsers', 'totalBK', 'totalSiswa',
            'totalDataKelas', 'totalClassroom', 'totalSiswaBelumKelas'
        ));
    }

    /**
     * API: live dashboard summary — stat cards.
     * Called every N seconds by the dashboard JS.
     */
    public function dashboardData(Request $request)
    {
        $totalBK              = BkAccount::count();
        $totalSiswa           = SiswaAccount::count();
        $totalUsers           = $totalBK + $totalSiswa;
        $totalDataKelas       = Kelas::count();
        $totalClassroom       = Classroom::count();
        $totalSiswaBelumKelas = SiswaAccount::whereNull('classroom_id')->count();

        return response()->json([
            'success'              => true,
            'totalUsers'           => $totalUsers,
            'totalBK'              => $totalBK,
            'totalSiswa'           => $totalSiswa,
            'totalDataKelas'       => $totalDataKelas,
            'totalClassroom'       => $totalClassroom,
            'totalSiswaBelumKelas' => $totalSiswaBelumKelas,
        ]);
    }

    /**
     * API: live message statistics for charts.
     * ?filter=live  → last 60 seconds, per second
     * ?filter=hour  → last 24 hours, per hour
     * ?filter=day   → last 30 days, per day
     */
    public function stats(Request $request)
    {
        $filter = $request->query('filter', 'hour');

        // Union both chat tables
        $chatSub      = \DB::table('chats')->select('created_at');
        $classroomSub = \DB::table('group_messages')->select('created_at');
        $union = $chatSub->unionAll($classroomSub);

        if ($filter === 'live') {
            // Per second — last 60 seconds
            $from   = now()->subSeconds(59)->startOfSecond();
            $counts = \DB::table(\DB::raw("({$union->toSql()}) as msgs"))
                ->mergeBindings($union)
                ->selectRaw("DATE_FORMAT(created_at, '%H:%i:%s') as label, COUNT(*) as total")
                ->where('created_at', '>=', $from)
                ->groupByRaw("DATE_FORMAT(created_at, '%H:%i:%s')")
                ->orderByRaw("created_at")
                ->pluck('total', 'label');

            // Build full 60-second range (fill gaps with 0)
            $labels = [];
            $values = [];
            for ($i = 59; $i >= 0; $i--) {
                $key = now()->subSeconds($i)->format('H:i:s');
                $labels[] = $key;
                $values[] = $counts[$key] ?? 0;
            }
        } elseif ($filter === 'hour') {
            // Per hour — last 24 hours
            $from   = now()->subHours(23)->startOfHour();
            $counts = \DB::table(\DB::raw("({$union->toSql()}) as msgs"))
                ->mergeBindings($union)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as label, COUNT(*) as total")
                ->where('created_at', '>=', $from)
                ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')")
                ->orderByRaw("created_at")
                ->pluck('total', 'label');

            $labels = [];
            $values = [];
            for ($i = 23; $i >= 0; $i--) {
                $dt  = now()->subHours($i)->startOfHour();
                $key = $dt->format('Y-m-d H:00:00');
                $labels[] = $dt->format('H:00');
                $values[] = $counts[$key] ?? 0;
            }
        } else {
            // Per day — last 30 days
            $from   = now()->subDays(29)->startOfDay();
            $counts = \DB::table(\DB::raw("({$union->toSql()}) as msgs"))
                ->mergeBindings($union)
                ->selectRaw("DATE(created_at) as label, COUNT(*) as total")
                ->where('created_at', '>=', $from)
                ->groupByRaw("DATE(created_at)")
                ->orderByRaw("created_at")
                ->pluck('total', 'label');

            $labels = [];
            $values = [];
            for ($i = 29; $i >= 0; $i--) {
                $key = now()->subDays($i)->format('Y-m-d');
                $labels[] = now()->subDays($i)->format('d/m');
                $values[] = $counts[$key] ?? 0;
            }
        }

        return response()->json([
            'success' => true,
            'filter'  => $filter,
            'labels'  => $labels,
            'values'  => $values,
            'total'   => array_sum($values),
        ]);
    }

    // ─── Account Management ──────────────────────────────────

    public function accounts(Request $request)
    {
        $bkAccounts    = BkAccount::orderBy('name')->paginate(15, ['*'], 'bk_page');
        $siswaAccounts = SiswaAccount::with('bk')->orderBy('name')->paginate(20, ['*'], 'siswa_page');
        $allBk         = BkAccount::orderBy('name')->get(['id', 'name', 'account_id']);

        return view('admin.sections.kelola_data.accounts', compact('bkAccounts', 'siswaAccounts', 'allBk'));
    }

    // ─── BK Account CRUD ─────────────────────────────────────

    public function storeBkAccount(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'login_id' => 'required|string|digits:18|unique:bk_account,login_id',
            'email'    => 'nullable|email|max:255|regex:/@gmail\.com$/i|unique:bk_account,email',
            'password' => 'required|string|min:6|max:12',
        ]);

        BkAccount::create([
            'name'                 => $validated['name'],
            'login_id'             => $validated['login_id'],
            'email'                => $validated['email'] ?? null,
            'password'             => Hash::make($validated['password']),
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.accounts.index')->with('success', 'Akun BK berhasil ditambahkan.');
    }

    public function updateBkAccount(Request $request, int $id)
    {
        $bk = BkAccount::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'login_id' => 'required|string|digits:18|unique:bk_account,login_id,' . $bk->id,
            'email'    => 'nullable|email|max:255|regex:/@gmail\.com$/i|unique:bk_account,email,' . $bk->id,
            'password' => 'nullable|string|min:6|max:12',
        ]);

        $bk->name     = $validated['name'];
        $bk->login_id = $validated['login_id'];
        $bk->email    = $validated['email'] ?? null;

        if (!empty($validated['password'])) {
            $bk->password             = Hash::make($validated['password']);
            $bk->must_change_password = false;
        }

        $bk->save();

        return redirect()->route('admin.accounts.index')->with('success', 'Akun BK berhasil diperbarui.');
    }

    public function deleteBkAccount(int $id)
    {
        $bk = BkAccount::findOrFail($id);
        $bk->delete();

        return redirect()->route('admin.accounts.index')->with('success', 'Akun BK berhasil dihapus.');
    }

    // ─── Siswa/i Account CRUD ────────────────────────────────

    public function storeSiswaAccount(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'login_id' => 'required|string|max:10|regex:/^\d+$/|unique:siswa_i_account,login_id',
            'email'    => 'nullable|email|max:255|regex:/@gmail\.com$/i|unique:siswa_i_account,email',
            'password' => 'required|string|min:6|max:12',
            'bk_id'    => 'nullable|integer|exists:bk_account,id',
        ]);

        SiswaAccount::create([
            'name'                 => $validated['name'],
            'login_id'             => $validated['login_id'],
            'email'                => $validated['email'] ?? null,
            'password'             => Hash::make($validated['password']),
            'bk_id'                => $validated['bk_id'] ?? null,
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.accounts.index')->with('success', 'Akun Siswa/i berhasil ditambahkan.');
    }

    public function updateSiswaAccount(Request $request, int $id)
    {
        $siswa = SiswaAccount::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'login_id' => 'required|string|max:10|regex:/^\d+$/|unique:siswa_i_account,login_id,' . $siswa->id,
            'email'    => 'nullable|email|max:255|regex:/@gmail\.com$/i|unique:siswa_i_account,email,' . $siswa->id,
            'password' => 'nullable|string|min:6|max:12',
            'bk_id'    => 'nullable|integer|exists:bk_account,id',
        ]);

        $siswa->name     = $validated['name'];
        $siswa->login_id = $validated['login_id'];
        $siswa->email    = $validated['email'] ?? null;
        $siswa->bk_id    = $validated['bk_id'] ?? null;

        if (!empty($validated['password'])) {
            $siswa->password             = Hash::make($validated['password']);
            $siswa->must_change_password = false;
        }

        $siswa->save();

        return redirect()->route('admin.accounts.index')->with('success', 'Akun Siswa/i berhasil diperbarui.');
    }

    public function deleteSiswaAccount(int $id)
    {
        $siswa = SiswaAccount::findOrFail($id);
        $siswa->delete();

        return redirect()->route('admin.accounts.index')->with('success', 'Akun Siswa/i berhasil dihapus.');
    }

    // ─── Assign BK to Siswa ──────────────────────────────────

    public function assignBk(Request $request, int $id)
    {
        $siswa = SiswaAccount::findOrFail($id);

        $request->validate([
            'bk_id' => 'required|integer|exists:bk_account,id',
        ]);

        $siswa->bk_id = $request->input('bk_id');
        $siswa->save();

        return redirect()->route('admin.accounts.index')->with('success', 'BK pembimbing berhasil ditetapkan.');
    }

    // ─── Import Accounts via Excel/Spreadsheet ───────────────

    public function importAccounts(Request $request)
    {
        $request->validate([
            'import_type' => 'required|in:bk,siswa',
            'file'        => 'required|file|mimes:xlsx,xls,csv,ods,gnumeric,xml|max:5120',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, .csv, atau .ods.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        // Use getRealPath() — avoids Windows mixed-separator issues from store()
        $fullPath = $request->file('file')->getRealPath();

        try {
            $importer = new UsersImport($request->input('import_type'));
            $result   = $importer->import($fullPath);
        } catch (\Throwable $e) {
            return redirect()->route('admin.accounts.index')
                ->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }

        $imported        = $result['imported'];
        $skipped         = $result['skipped'];
        $errors          = $result['errors'];
        $importedRecords = $result['imported_records'] ?? [];

        // Format a structured error entry into a display string with ✗ prefix
        $fmtError = function (array $e): string {
            if (!empty($e['name']) && !empty($e['id'])) {
                return "✗ {$e['name']}, {$e['id']}, {$e['reason']}";
            } elseif (!empty($e['name'])) {
                return "✗ {$e['name']}, {$e['reason']}";
            }
            return "✗ {$e['reason']}";
        };

        if ($imported === 0 && !empty($errors)) {
            $msg = 'Import gagal. Tidak ada akun yang berhasil diimpor.';
            return redirect()->route('admin.accounts.index')
                ->with('error', $msg)
                ->with('flash_notes', array_map($fmtError, $errors));
        }

        $msg = "Berhasil mengimpor {$imported} akun.";
        if (!empty($errors)) $msg .= ' Beberapa baris memiliki catatan.';

        $notes = array_merge(
            array_map(fn ($r) => "✓ {$r['name']}, {$r['id']}", $importedRecords),
            array_map($fmtError, $errors)
        );

        return redirect()->route('admin.accounts.index')
            ->with('success', $msg)
            ->with('flash_notes', $notes);
    }
}
