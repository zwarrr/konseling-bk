<?php

namespace App\Http\Controllers;

use App\Models\AdminAccount;
use App\Models\BkAccount;
use App\Models\SiswaAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class AuthController extends Controller
{
    /**
     * Try to find a user by role ID fields (login_id/account_id), then optional email.
     */
    private function findByIdentifier(string $modelClass, string $identifier, bool $allowEmail = true): ?Model
    {
        $q = $modelClass::query()
            ->where('login_id', $identifier)
            ->orWhere('account_id', $identifier);

        if ($allowEmail && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $q->orWhere('email', $identifier);
        }

        return $q->first();
    }

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

        $identifier = trim((string) $validated['id']);

        // ── Cek admin dulu ────────────────────────────────────────────────
        // Admin table does not have email, so admin uses login_id/account_id.
        $admin = $this->findByIdentifier(AdminAccount::class, $identifier, false);
        if ($admin && Hash::check($validated['password'], $admin->password)) {
            // Ensure we never keep BK/Siswa sessions alongside admin in the same browser session.
            Auth::guard('bk')->logout();
            Auth::guard('siswa')->logout();
            Auth::guard('admin')->login($admin, false);
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard.index');
        }

        // ── BK / Siswa login ──────────────────────────────────────────────
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        $isNumericId = preg_match('/^\d+$/', $identifier) === 1;

        $candidates = [];
        if ($isEmail) {
            $candidates[] = ['guard' => 'bk', 'user' => $this->findByIdentifier(BkAccount::class, $identifier, true)];
            $candidates[] = ['guard' => 'siswa', 'user' => $this->findByIdentifier(SiswaAccount::class, $identifier, true)];
        } elseif ($isNumericId && strlen($identifier) === 18) {
            // Keep previous behavior for NIP-like IDs: prioritize BK.
            $candidates[] = ['guard' => 'bk', 'user' => $this->findByIdentifier(BkAccount::class, $identifier, false)];
            $candidates[] = ['guard' => 'siswa', 'user' => $this->findByIdentifier(SiswaAccount::class, $identifier, false)];
        } else {
            // Default role-ID flow: siswa first, fallback to BK.
            $candidates[] = ['guard' => 'siswa', 'user' => $this->findByIdentifier(SiswaAccount::class, $identifier, false)];
            $candidates[] = ['guard' => 'bk', 'user' => $this->findByIdentifier(BkAccount::class, $identifier, false)];
        }

        $user = null;
        $guard = null;
        foreach ($candidates as $candidate) {
            $candidateUser = $candidate['user'] ?? null;
            if ($candidateUser && Hash::check($validated['password'], $candidateUser->password)) {
                $user = $candidateUser;
                $guard = $candidate['guard'];
                break;
            }
        }

        if (!$user || !$guard) {
            return back()->withErrors(['id' => 'ID / password tidak valid.'])->withInput();
        }

        // Ensure we never keep multiple role sessions at once.
        // This prevents must.setup and role routing from picking the wrong guard.
        Auth::guard('admin')->logout();
        if ($guard === 'bk') {
            Auth::guard('siswa')->logout();
        } else {
            Auth::guard('bk')->logout();
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
        // Safety: clear all guards so sessions don't bleed across roles/devices (PWA vs browser).
        Auth::guard('admin')->logout();
        Auth::guard('bk')->logout();
        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }

    public function logoutAdmin(Request $request)
    {
        // Safety: clear all guards so sessions don't bleed across roles/devices (PWA vs browser).
        Auth::guard('bk')->logout();
        Auth::guard('siswa')->logout();
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
