<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramSection;

class ProgramSectionSeeder extends Seeder
{
    public function run(): void
    {
        ProgramSection::updateOrCreate([], [
            'title'       => 'Kegiatan & Program Terbaru',
            'description' => 'Informasi kegiatan bimbingan dan konseling yang sedang dan akan datang untuk mendukung perkembangan siswa.',
        ]);
    }
}
