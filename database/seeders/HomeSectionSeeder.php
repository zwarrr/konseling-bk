<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomeSection;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        HomeSection::firstOrCreate([], [
            'title'       => 'Tumbuh Bersama',
            'subtitle'    => 'Bimbingan & Konseling',
            'description' => 'Platform digital BIKASI yang menghubungkan siswa dengan guru konselor secara mudah, privat, dan profesional — kapan saja, di mana saja.',
            'img'         => null,
        ]);
    }
}
