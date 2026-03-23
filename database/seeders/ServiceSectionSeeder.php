<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceSection;

class ServiceSectionSeeder extends Seeder
{
    public function run(): void
    {
        ServiceSection::updateOrCreate([], [
            'title'       => 'Kelola Semua Kebutuhan BK',
            'subtitle'    => 'Gunakan E-Konseling untuk',
            'description' => 'Platform digital konseling yang dirancang untuk mendukung perkembangan siswa secara menyeluruh — kapan saja, di mana saja.',
            'img'         => 'favicon.png',
        ]);
    }
}
