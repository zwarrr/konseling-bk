<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomeSection;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $home = HomeSection::firstOrCreate([], [
            'title'       => 'Tumbuh Bersama',
            'subtitle'    => 'Bimbingan & Konseling',
            'description' => 'Platform digital E-Konseling yang menghubungkan siswa dengan guru konselor secara mudah, privat, dan profesional — kapan saja, di mana saja.',
            'img'         => 'assets/img/promot_iphone3d.png',
        ]);

        if ($home->img === null || $home->img === '' || $home->img === 'assets/img/iPhone.png') {
            $home->update(['img' => 'assets/img/promot_iphone3d.png']);
        }
    }
}
