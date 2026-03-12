<?php

namespace Database\Seeders;

use App\Models\AdminAccount;
use App\Models\BkAccount;
use App\Models\Chat;
use App\Models\Classroom;
use App\Models\Kelas;
use App\Models\SiswaAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seed akun untuk login:
 *
 *  ADMIN
 *   login_id : 1        password : admin123
 *
 *  BK (login_id harus 18 digit angka)
 *   login_id : 196601011994031001   password : bk123456   (Pak Budi)
 *   login_id : 197703152005042002   password : bk123456   (Bu Sari)
 *
 *  SISWA (login_id ≤ 10 digit angka)
 *   login_id : 2026001     password : siswa123   (Andi)
 *   login_id : 2026002     password : siswa123   (Bela)
 *   login_id : 2026003     password : siswa123   (Cahyo)
 *
 *  KELAS  : XII RPL (BK01)  |  XII IPA (BK02)
 *  SISWA → kelas : Andi & Bela → XII RPL, Cahyo → XII IPA
 */
class AuthSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ─────────────────────────────────────────────────────────
        AdminAccount::updateOrCreate(
            ['login_id' => '1'],
            [
                'name'                 => 'Administrator',
                'account_id'           => 'ADM1',
                'password'             => Hash::make('admin123'),
                'must_change_password' => false,
            ]
        );

        // ── Akun BK ───────────────────────────────────────────────────────
        $bk1 = BkAccount::updateOrCreate(
            ['login_id' => '196601011994031001'],
            [
                'name'                 => 'Budi Santoso, S.Pd',
                'account_id'           => 'BK01',
                'email'                => null,
                'password'             => Hash::make('bk123456'),
                'must_change_password' => true,
            ]
        );

        $bk2 = BkAccount::updateOrCreate(
            ['login_id' => '197703152005042002'],
            [
                'name'                 => 'Sari Dewi, S.Psi',
                'account_id'           => 'BK02',
                'email'                => null,
                'password'             => Hash::make('bk123456'),
                'must_change_password' => true,
            ]
        );

        // ── Kelas  (kelas master → menentukan siapa BK-nya) ───────────────
        $kelas1 = Kelas::updateOrCreate(
            ['kelas' => 'XII', 'jurusan' => 'RPL'],
            ['jumlah_siswa_i' => 17, 'bk_id' => $bk1->id]
        );

        $kelas2 = Kelas::updateOrCreate(
            ['kelas' => 'XII', 'jurusan' => 'IPA'],
            ['jumlah_siswa_i' => 16, 'bk_id' => $bk2->id]
        );

        // ── Classrooms (grup chat per kelas) ──────────────────────────────
        // firstOrCreate so join_token is never regenerated on re-seed
        $classroom1 = Classroom::where('name', 'XII RPL')->first();
        if (!$classroom1) {
            $classroom1 = new Classroom([
                'id'            => 'KLS01',
                'name'          => 'XII RPL',
                'bk_account_id' => 'BK01',
                'class_id'      => $kelas1->id,
                'description'   => 'Grup kelas XII RPL',
                'join_token'    => Str::random(12),
            ]);
            $classroom1->save();
        } else {
            $classroom1->update(['bk_account_id' => 'BK01', 'class_id' => $kelas1->id]);
        }

        $classroom2 = Classroom::where('name', 'XII IPA')->first();
        if (!$classroom2) {
            $classroom2 = new Classroom([
                'id'            => 'KLS02',
                'name'          => 'XII IPA',
                'bk_account_id' => 'BK02',
                'class_id'      => $kelas2->id,
                'description'   => 'Grup kelas XII IPA',
                'join_token'    => Str::random(12),
            ]);
            $classroom2->save();
        } else {
            $classroom2->update(['bk_account_id' => 'BK02', 'class_id' => $kelas2->id]);
        }

        // ── Akun Siswa/i (classroom_id → BK otomatis dari kelas) ─────────
        $siswa1 = SiswaAccount::updateOrCreate(
            ['login_id' => '2026001'],
            [
                'name'                 => 'Andi Pratama',
                'account_id'           => 'SSWA01',
                'email'                => null,
                'absen'                => 1,
                'classroom_id'         => $classroom1->id,
                'bk_id'                => $bk1->id,
                'password'             => Hash::make('siswa123'),
                'must_change_password' => true,
            ]
        );

        $siswa2 = SiswaAccount::updateOrCreate(
            ['login_id' => '2026002'],
            [
                'name'                 => 'Bela Safitri',
                'account_id'           => 'SSWA02',
                'email'                => null,
                'absen'                => 2,
                'classroom_id'         => $classroom1->id,
                'bk_id'                => $bk1->id,
                'password'             => Hash::make('siswa123'),
                'must_change_password' => true,
            ]
        );

        SiswaAccount::updateOrCreate(
            ['login_id' => '2026003'],
            [
                'name'                 => 'Cahyo Nugroho',
                'account_id'           => 'SSWA03',
                'email'                => null,
                'absen'                => 1,
                'classroom_id'         => $classroom2->id,
                'bk_id'                => $bk2->id,
                'password'             => Hash::make('siswa123'),
                'must_change_password' => true,
            ]
        );

        // ── Contoh pesan chat awal (agar list chat BK tidak kosong) ───────
        if ($bk1 && $siswa1) {
            $si     = strtoupper(str_pad(substr(preg_replace('/[^a-zA-Z]/', '', $siswa1->name), 0, 2), 2, 'X'));
            $gi     = strtoupper(str_pad(substr(preg_replace('/[^a-zA-Z]/', '', $bk1->name), 0, 2), 2, 'X'));
            $roomId = 'RC' . $si . $gi . '01';

            Chat::firstOrCreate(
                ['room_id' => $roomId, 'message' => 'Halo Pak Budi, saya ingin konseling.'],
                [
                    'siswa_account_id'  => $siswa1->account_id,
                    'guru_account_id'   => $bk1->account_id,
                    'sender_role'       => 'siswa',
                    'sender_account_id' => $siswa1->account_id,
                    'message_type'      => 'text',
                    'status'            => 'unread',
                ]
            );
        }
    }
}
