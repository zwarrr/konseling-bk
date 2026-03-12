<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['icon' => 'fa-comments',       'title' => 'Konseling Individual',  'description' => 'Sesi privat 1:1 dengan konselor profesional yang siap mendampingi setiap siswa.',           'sort_order' => 1],
            ['icon' => 'fa-calendar-check', 'title' => 'Jadwal Bimbingan',      'description' => 'Atur dan pantau jadwal sesi bimbingan secara mudah melalui platform digital.',              'sort_order' => 2],
            ['icon' => 'fa-chart-line',     'title' => 'Pantau Perkembangan',   'description' => 'Lacak progres belajar dan perkembangan pribadi siswa secara terstruktur.',                  'sort_order' => 3],
            ['icon' => 'fa-book-open',      'title' => 'Konten Edukasi',        'description' => 'Akses artikel, video, dan materi bimbingan yang dikurasi oleh tim konselor berpengalaman.', 'sort_order' => 4],
            ['icon' => 'fa-shield-heart',   'title' => 'Kesehatan Mental',      'description' => 'Layanan skrining dan pendampingan kesehatan mental siswa secara berkala dan terukur.',      'sort_order' => 5],
            ['icon' => 'fa-graduation-cap', 'title' => 'Persiapan Karier',      'description' => 'Panduan eksplorasi minat, bakat, dan perencanaan karier untuk masa depan siswa.',         'sort_order' => 6],
        ];

        foreach ($items as $item) {
            Service::firstOrCreate(['title' => $item['title']], $item);
        }
    }
}
