<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account (numeric login)
        User::updateOrCreate(
            ['role' => 'admin', 'account_id_nip_nis' => 999999],
            [
                'name' => 'Admin',
                'email' => null,
                'password' => Hash::make('999999'),
            ]
        );

        // BK account (NIP + numeric password)
        $bk = User::updateOrCreate(
            ['role' => 'guru', 'account_id_nip_nis' => 12345678],
            [
                'name' => 'Guru BK',
                'email' => null,
                'password' => Hash::make('1234'),
            ]
        );

        // One siswa contact (NIS + numeric password)
        $siswa = User::updateOrCreate(
            ['role' => 'siswa', 'account_id_nip_nis' => 20260001],
            [
                'name' => 'Siswa Demo',
                'email' => null,
                'password' => Hash::make('1234'),
            ]
        );

        // Create one initial chat message so BK sees 1 contact/session.
        // We use room_name/phone_number = NIS so it stays numeric.
        Chat::firstOrCreate(
            [
                'room_name' => (string) $siswa->account_id_nip_nis,
                'phone_number' => (string) $siswa->account_id_nip_nis,
                'message' => 'Halo BK, saya butuh konseling.',
                'direction' => 'incoming',
            ],
            [
                'sender_name' => $siswa->name,
                'sender_role' => 'siswa',
                'message_type' => 'text',
                'status' => 'delivered',
                'is_read' => false,
            ]
        );
    }
}
