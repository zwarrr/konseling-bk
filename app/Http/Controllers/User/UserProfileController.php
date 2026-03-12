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
