<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgendaSection;

class AgendaSectionSeeder extends Seeder
{
    public function run(): void
    {
        AgendaSection::updateOrCreate([], [
            'title'       => 'Kegiatan & Program Terbaru',
            'description' => 'Informasi kegiatan bimbingan dan konseling yang sedang dan akan datang untuk mendukung perkembangan siswa.',
        ]);
    }
}
