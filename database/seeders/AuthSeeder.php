<?php

namespace Database\Seeders;

use App\Models\AdminAccount;
use App\Models\BkAccount;
use App\Models\SiswaAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed akun login minimal:
 *
 *  ADMIN  : login_id 1                     | password admin123
 *  BK     : login_id 196601011994031001    | password bk123456
 *  SISWA  : login_id 2026001               | password siswa123
 */
class AuthSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ─────────────────────────────────────────────────────────
        $admin = AdminAccount::firstOrNew(['login_id' => '1']);
        if (!$admin->exists) {
            $admin->fill([
                'name'                 => 'Administrator',
                'account_id'           => 'ADM1',
                'password'             => Hash::make('admin123'),
                'must_change_password' => false,
            ]);
            $admin->save();
        }

        // ── Akun BK (1 akun) ─────────────────────────────────────────────
        $bk1 = BkAccount::firstOrNew(['login_id' => '196601011994031001']);
        if (!$bk1->exists) {
            $bk1->fill([
                'name'                 => 'Budi Santoso, S.Pd',
                'account_id'           => 'BK01',
                'email'                => null,
                'password'             => Hash::make('bk123456'),
                'must_change_password' => true,
            ]);
            $bk1->save();
        }

        // ── Akun Siswa (1 akun) ───────────────────────────────────────────
        $siswa1 = SiswaAccount::firstOrNew(['login_id' => '2026001']);
        if (!$siswa1->exists) {
            $siswa1->fill([
                'name'                 => 'Andi Pratama',
                'account_id'           => 'SSWA01',
                'email'                => null,
                'absen'                => 1,
                'classroom_id'         => null,
                'bk_id'                => $bk1->id,
                'password'             => Hash::make('siswa123'),
                'must_change_password' => true,
            ]);
            $siswa1->save();
        }
    }
}
