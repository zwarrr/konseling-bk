<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $role = $user->role ?? null;

        $path = '/' . ltrim($request->path(), '/');
        if (str_starts_with($path, '/bk/')    && $role !== 'guru') abort(403);
        if (str_starts_with($path, '/siswa/') && $role !== 'siswa') abort(403);

        $homeRoute = ($role === 'guru') ? route('bk.home') : route('siswa.home');

        return view('users.sections.profile', compact('user', 'homeRoute'));
    }

    public function guide(Request $request)
    {
        $user = $request->user();
        $role = $user->role ?? null;

        $path = '/' . ltrim($request->path(), '/');
        if (str_starts_with($path, '/bk/')    && $role !== 'guru') abort(403);
        if (str_starts_with($path, '/siswa/') && $role !== 'siswa') abort(403);

        $isBk = $role === 'guru';
        $backRoute = $isBk ? route('bk.profile') : route('siswa.profile');
        $roleLabel = $isBk ? 'Guru BK' : 'Siswa/i';

        $steps = $isBk
            ? [
                [
                    'title' => 'Kelola Program dan Kegiatan',
                    'items' => [
                        'Buka menu Program untuk melihat daftar kegiatan terbaru.',
                        'Gunakan tombol Kelola untuk tambah, edit, atau hapus program.',
                        'Cek menu ACC Booking untuk menyetujui atau menolak pengajuan siswa.',
                    ],
                ],
                [
                    'title' => 'Kelola Kelas dan Kelompok',
                    'items' => [
                        'Masuk ke menu Kelas untuk menata siswa per kelas binaan.',
                        'Buat kelompok diskusi sesuai kebutuhan pembinaan.',
                        'Pastikan data anggota kelas selalu sinkron agar chat kelompok tepat sasaran.',
                    ],
                ],
                [
                    'title' => 'Komunikasi dan Monitoring',
                    'items' => [
                        'Gunakan menu Chat untuk komunikasi personal dengan siswa.',
                        'Pantau notifikasi agar tidak ada pesan atau booking yang terlewat.',
                        'Perbarui profil agar siswa mudah mengenali pembimbingnya.',
                    ],
                ],
            ]
            : [
                [
                    'title' => 'Jelajahi Program',
                    'items' => [
                        'Buka menu Program untuk melihat kegiatan BK yang tersedia.',
                        'Masuk ke detail program untuk membaca informasi lengkap.',
                        'Gunakan tombol booking jika ingin ikut program yang dijadwalkan.',
                    ],
                ],
                [
                    'title' => 'Chat dan Konsultasi',
                    'items' => [
                        'Gunakan menu Chat untuk konsultasi dengan Guru BK pembimbing.',
                        'Sampaikan pertanyaan secara jelas agar respon lebih cepat dan tepat.',
                        'Aktifkan notifikasi supaya tidak tertinggal balasan penting.',
                    ],
                ],
                [
                    'title' => 'Kelola Akun Pribadi',
                    'items' => [
                        'Perbarui profil dan email pada menu Profil secara berkala.',
                        'Jaga kerahasiaan password dan ganti jika diperlukan.',
                        'Cek menu Berita untuk informasi BK terbaru dari sekolah.',
                    ],
                ],
            ];

        return view('users.sections.guide', compact('user', 'roleLabel', 'backRoute', 'steps'));
    }

    public function update(Request $request)
    {
        $user  = auth()->user();
        $rules = [
            'name'  => 'required|string|max:255',
            'about' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|regex:/@gmail\.com$/i|unique:' . $user->getTable() . ',email,' . $user->id,
        ];
        if ($request->filled('new_password')) {
            $rules['new_password'] = 'required|min:6|max:12|confirmed';
        }

        $validated = $request->validate($rules);

        if ($request->filled('new_password')) {
            $user->password             = Hash::make($validated['new_password']);
            $user->must_change_password = false;
        }
        $user->name  = $validated['name'];
        $user->about = $validated['about'] ?? null;
        $user->email = $validated['email'] ?? null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Profil berhasil diperbarui.']);
    }

    public function forceChangePassword(Request $request)
    {
        $validated = $request->validate([
            'new_password' => 'required|min:6|max:12|confirmed',
        ]);

        $user = auth()->user();
        $user->password             = Hash::make($validated['new_password']);
        $user->must_change_password = false;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password berhasil diubah.']);
    }
}
