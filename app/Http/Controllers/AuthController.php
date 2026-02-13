<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $routeName = (string) ($request->route()?->getName() ?? '');

        // Guard-aware redirect: block access to the login page if that guard is already authenticated.
        if ($routeName === 'admin.login') {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('admin.dashboard.index');
            }
        } else {
            if (Auth::guard('web')->check()) {
                $role = Auth::guard('web')->user()?->role;
                return ($role === 'guru')
                    ? redirect()->route('bk.chat')
                    : redirect()->route('siswa.chat');
            }
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'regex:/^\d+$/'],
            'password' => ['required', 'regex:/^\d+$/'],
        ]);

        $id = (int) $validated['id'];

        $user = User::query()->where('account_id_nip_nis', $id)->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'id' => 'ID / password tidak valid.',
            ])->withInput();
        }

        $targetGuard = (($user->role ?? null) === 'admin') ? 'admin' : 'web';
        $guard = Auth::guard($targetGuard);

        // Only switch/logout within the same guard. Keep the other guard logged in.
        if ($guard->check() && (int) $guard->id() !== (int) $user->id) {
            $guard->logout();
        }

        $guard->login($user);
        $request->session()->regenerate();

        if (($user->role ?? null) === 'admin') {
            return redirect()->route('admin.dashboard.index');
        }

        return ($user->role ?? null) === 'guru'
            ? redirect()->route('bk.chat')
            : redirect()->route('siswa.chat');
    }

    public function logout(Request $request)
    {
        // Web logout only; keep admin session if any.
        Auth::guard('web')->logout();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }

    public function logoutAdmin(Request $request)
    {
        // Admin logout only; keep web session if any.
        Auth::guard('admin')->logout();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }
}
