<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AboutFeature;

class AboutFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['title' => 'Konsultasi Karier',   'description' => 'Bimbingan pilihan studi & jalur karier profesional', 'sort_order' => 1],
            ['title' => 'Kesehatan Mental',     'description' => 'Dukungan emosional & manajemen stres siswa',          'sort_order' => 2],
            ['title' => 'Mediasi Konflik',      'description' => 'Penyelesaian konflik antar siswa secara damai',        'sort_order' => 3],
            ['title' => 'Layanan Digital 24/7', 'description' => 'Akses layanan BK kapan saja melalui platform kami',   'sort_order' => 4],
        ];

        foreach ($features as $feat) {
            AboutFeature::firstOrCreate(['title' => $feat['title']], $feat);
        }
    }
}
