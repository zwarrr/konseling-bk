<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ─── Dashboard ───────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $totalMessages = Chat::count();
        $totalUsers = User::count();
        $totalBK = User::where('role', 'guru')->count();
        $totalSiswa = User::where('role', 'siswa')->count();

        return view('admin.sections.dashboard.index', compact('totalMessages', 'totalUsers', 'totalBK', 'totalSiswa'));
    }

    /**
     * API: message statistics for charts.
     * ?filter=minute|hour|day|month  (default: day)
     */
    public function stats(Request $request)
    {
        $filter = $request->query('filter', 'day');

        $driver = DB::getDriverName();

        switch ($filter) {
            case 'minute':
                if ($driver === 'sqlite') {
                    $groupExpr = "strftime('%Y-%m-%d %H:%M', created_at)";
                } else {
                    $groupExpr = "DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')";
                }
                $labelFormat = 'H:i';
                break;

            case 'hour':
                if ($driver === 'sqlite') {
                    $groupExpr = "strftime('%Y-%m-%d %H', created_at)";
                } else {
                    $groupExpr = "DATE_FORMAT(created_at, '%Y-%m-%d %H')";
                }
                $labelFormat = 'H:00';
                break;

            case 'month':
                if ($driver === 'sqlite') {
                    $groupExpr = "strftime('%Y-%m', created_at)";
                } else {
                    $groupExpr = "DATE_FORMAT(created_at, '%Y-%m')";
                }
                $labelFormat = 'M Y';
                break;

            default: // day
                if ($driver === 'sqlite') {
                    $groupExpr = "strftime('%Y-%m-%d', created_at)";
                } else {
                    $groupExpr = "DATE_FORMAT(created_at, '%Y-%m-%d')";
                }
                $labelFormat = 'd M';
                break;
        }

        $rows = Chat::selectRaw("$groupExpr as period, count(*) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->limit(60)
            ->get();

        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            try {
                $dt = new \DateTime($row->period);
                $labels[] = $dt->format($labelFormat);
            } catch (\Throwable $e) {
                $labels[] = $row->period;
            }
            $values[] = (int) $row->total;
        }

        return response()->json([
            'success' => true,
            'filter' => $filter,
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    // ─── Account Management ──────────────────────────────────

    public function accounts(Request $request)
    {
        $users = User::orderByRaw("CASE role WHEN 'admin' THEN 0 WHEN 'guru' THEN 1 WHEN 'siswa' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get();

        return view('admin.sections.accounts.index', compact('users'));
    }

    public function createAccount()
    {
        $user = new User();
        $user->role = 'siswa';

        return view('admin.sections.accounts.form', [
            'mode' => 'create',
            'user' => $user,
        ]);
    }

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:guru,siswa',
            'account_id_nip_nis' => 'required|digits_between:1,20|unique:users,account_id_nip_nis',
            'password' => 'required|digits_between:4,20',
        ]);

        User::create([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'account_id_nip_nis' => (int) $validated['account_id_nip_nis'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.accounts.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function editAccount(User $user)
    {
        return view('admin.sections.accounts.form', [
            'mode' => 'edit',
            'user' => $user,
        ]);
    }

    public function updateAccount(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:guru,siswa',
            'account_id_nip_nis' => 'required|digits_between:1,20|unique:users,account_id_nip_nis,' . $user->id,
            'password' => 'nullable|digits_between:4,20',
        ]);

        $user->name = $validated['name'];
        $user->role = $validated['role'];
        $user->account_id_nip_nis = (int) $validated['account_id_nip_nis'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function deleteAccount(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.accounts.index')->with('error', 'Akun admin tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.accounts.index')->with('success', 'Akun berhasil dihapus.');
    }
}
