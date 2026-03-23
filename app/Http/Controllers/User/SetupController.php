<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\SetupCompletedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SetupController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user->must_change_password) {
            return redirect()->route($user->role === 'guru' ? 'bk.home' : 'siswa.home');
        }

        return view('auth.setup', ['user' => $user]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'email'                 => ['required', 'email', 'ends_with:@gmail.com', 'unique:' . $user->getTable() . ',email,' . $user->id],
            'password'              => ['required', 'min:6', 'max:12', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.ends_with'    => 'Email harus menggunakan @gmail.com.',
            'email.unique'       => 'Email sudah digunakan akun lain.',
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.max'       => 'Password maksimal 12 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ]);

        $user->update([
            'email'                => $validated['email'],
            'password'             => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        if (config('mail.notifications_enabled')) {
            try {
                Mail::to($validated['email'])->send(new SetupCompletedMail(
                    (string) $user->name,
                    (string) ($user->role ?? 'user')
                ));
            } catch (\Throwable $e) {
                // Do not block setup completion when SMTP is temporarily unavailable.
                Log::warning('Gagal mengirim email notifikasi setup akun.', [
                    'user_id' => $user->id,
                    'table'   => $user->getTable(),
                    'email'   => $validated['email'],
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route($user->role === 'guru' ? 'bk.home' : 'siswa.home')
            ->with('flash_success', 'Profil berhasil disiapkan. Selamat datang, ' . $user->name . '!');
    }
}
