<?php

namespace Database\Seeders;

use App\Models\BkAccount;
use App\Models\Classroom;
use App\Models\SiswaAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed 30 akun siswa (15 per kelas):
 *
 *  ── XII RPL (KLS01 / BK01) ──────────────────────────────────────────
 *   SSWA04  2026004  Doni Setiawan
 *   SSWA05  2026005  Eva Ramadhani
 *   SSWA06  2026006  Fajar Maulana
 *   SSWA07  2026007  Gita Permata
 *   SSWA08  2026008  Hendra Wijaya
 *   SSWA09  2026009  Indah Lestari
 *   SSWA10  2026010  Joko Susanto
 *   SSWA11  2026011  Kartika Sari
 *   SSWA12  2026012  Lukman Hakim
 *   SSWA13  2026013  Maya Anggraini
 *   SSWA14  2026014  Naufal Rizky
 *   SSWA15  2026015  Olivia Putri
 *   SSWA16  2026016  Pandu Kurniawan
 *   SSWA17  2026017  Qori Amalia
 *   SSWA18  2026018  Reza Firmansyah
 *
 *  ── XII IPA (KLS02 / BK02) ──────────────────────────────────────────
 *   SSWA19  2026019  Sinta Wulandari
 *   SSWA20  2026020  Taufik Hidayat
 *   SSWA21  2026021  Umi Kulsum
 *   SSWA22  2026022  Vina Apriani
 *   SSWA23  2026023  Wahyu Prasetyo
 *   SSWA24  2026024  Xena Maharani
 *   SSWA25  2026025  Yoga Pratama
 *   SSWA26  2026026  Zahra Nuranisa
 *   SSWA27  2026027  Agus Salim
 *   SSWA28  2026028  Bunga Citra
 *   SSWA29  2026029  Candra Irawan
 *   SSWA30  2026030  Dewi Mustika
 *   SSWA31  2026031  Eko Santoso
 *   SSWA32  2026032  Fitri Handayani
 *   SSWA33  2026033  Gilang Ramadhan
 *
 *  Password awal = login_id (siswa harus ganti saat login pertama)
 */
class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $bk1 = BkAccount::where('account_id', 'BK01')->first();
        $bk2 = BkAccount::where('account_id', 'BK02')->first();

        $cls1 = Classroom::where('id', 'KLS01')->first();
        $cls2 = Classroom::where('id', 'KLS02')->first();

        if (!$bk1 || !$bk2 || !$cls1 || !$cls2) {
            $this->command->warn('SiswaSeeder: AuthSeeder harus dijalankan terlebih dahulu.');
            return;
        }

        // ── XII RPL (15 siswa) ────────────────────────────────────────────
        $rpl = [
            ['SSWA04', '2026004', 'Doni Setiawan',      3],
            ['SSWA05', '2026005', 'Eva Ramadhani',      4],
            ['SSWA06', '2026006', 'Fajar Maulana',      5],
            ['SSWA07', '2026007', 'Gita Permata',       6],
            ['SSWA08', '2026008', 'Hendra Wijaya',      7],
            ['SSWA09', '2026009', 'Indah Lestari',      8],
            ['SSWA10', '2026010', 'Joko Susanto',       9],
            ['SSWA11', '2026011', 'Kartika Sari',      10],
            ['SSWA12', '2026012', 'Lukman Hakim',      11],
            ['SSWA13', '2026013', 'Maya Anggraini',    12],
            ['SSWA14', '2026014', 'Naufal Rizky',      13],
            ['SSWA15', '2026015', 'Olivia Putri',      14],
            ['SSWA16', '2026016', 'Pandu Kurniawan',   15],
            ['SSWA17', '2026017', 'Qori Amalia',       16],
            ['SSWA18', '2026018', 'Reza Firmansyah',   17],
        ];

        foreach ($rpl as [$accountId, $loginId, $name, $absen]) {
            SiswaAccount::firstOrCreate(
                ['login_id' => $loginId],
                [
                    'name'                 => $name,
                    'account_id'           => $accountId,
                    'absen'                => $absen,
                    'classroom_id'         => $cls1->id,
                    'bk_id'                => $bk1->id,
                    'password'             => Hash::make($loginId),
                    'must_change_password' => true,
                ]
            );
        }

        // ── XII IPA (15 siswa) ────────────────────────────────────────────
        $ipa = [
            ['SSWA19', '2026019', 'Sinta Wulandari',    2],
            ['SSWA20', '2026020', 'Taufik Hidayat',     3],
            ['SSWA21', '2026021', 'Umi Kulsum',          4],
            ['SSWA22', '2026022', 'Vina Apriani',        5],
            ['SSWA23', '2026023', 'Wahyu Prasetyo',      6],
            ['SSWA24', '2026024', 'Xena Maharani',       7],
            ['SSWA25', '2026025', 'Yoga Pratama',        8],
            ['SSWA26', '2026026', 'Zahra Nuranisa',      9],
            ['SSWA27', '2026027', 'Agus Salim',         10],
            ['SSWA28', '2026028', 'Bunga Citra',        11],
            ['SSWA29', '2026029', 'Candra Irawan',      12],
            ['SSWA30', '2026030', 'Dewi Mustika',       13],
            ['SSWA31', '2026031', 'Eko Santoso',        14],
            ['SSWA32', '2026032', 'Fitri Handayani',    15],
            ['SSWA33', '2026033', 'Gilang Ramadhan',    16],
        ];

        foreach ($ipa as [$accountId, $loginId, $name, $absen]) {
            SiswaAccount::firstOrCreate(
                ['login_id' => $loginId],
                [
                    'name'                 => $name,
                    'account_id'           => $accountId,
                    'absen'                => $absen,
                    'classroom_id'         => $cls2->id,
                    'bk_id'                => $bk2->id,
                    'password'             => Hash::make($loginId),
                    'must_change_password' => true,
                ]
            );
        }
    }
}
