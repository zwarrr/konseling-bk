<?php

namespace App\Http\Controllers;

use App\Models\AdminAccount;
use App\Models\BkAccount;
use App\Models\SiswaAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard.index');
        }
        if (Auth::guard('bk')->check()) {
            return redirect()->route('bk.home');
        }
        if (Auth::guard('siswa')->check()) {
            return redirect()->route('siswa.home');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'id'       => ['required', 'string'],
            'password' => ['required', 'min:6', 'max:18'],
        ]);

        $id = $validated['id'];

        // ── Cek admin dulu ────────────────────────────────────────────────
        $admin = AdminAccount::where('login_id', $id)->first();
        if ($admin && Hash::check($validated['password'], $admin->password)) {
            Auth::guard('admin')->login($admin, false);
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard.index');
        }

        // ── BK / Siswa login ──────────────────────────────────────────────
        // 18 digit angka = NIP (BK), selainnya = NIS (Siswa)
        $idLength = strlen(preg_replace('/\D/', '', $id));

        if ($idLength === 18) {
            $user  = BkAccount::where('login_id', $id)->first();
            $guard = 'bk';
        } else {
            $user = SiswaAccount::where('login_id', $id)->first();
            if (!$user) {
                $user  = BkAccount::where('login_id', $id)->first();
                $guard = 'bk';
            } else {
                $guard = 'siswa';
            }
        }

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['id' => 'ID / password tidak valid.'])->withInput();
        }

        $authGuard = Auth::guard($guard);
        if ($authGuard->check() && (int) $authGuard->id() !== (int) $user->id) {
            $authGuard->logout();
        }

        $authGuard->login($user, true);
        $request->session()->regenerate();

        if ($user->must_change_password) {
            return redirect()->route('user.setup');
        }

        return ($guard === 'bk')
            ? redirect()->route('bk.home')
            : redirect()->route('siswa.home');
    }

    public function logout(Request $request)
    {
        Auth::guard('bk')->logout();
        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }

    public function logoutAdmin(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // During maintenance, redirect to the secret admin URL instead of normal login
        if (\App\Models\AppSetting::maintenanceMode()) {
            $secret = trim(\App\Models\AppSetting::get('maintenance_admin_url', 'ginlogin'), '/ ');
            return response()->json(['success' => true, 'redirect' => url('auth/' . $secret)]);
        }

        return response()->json(['success' => true]);
    }
}
