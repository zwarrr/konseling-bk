<?php

namespace Database\Seeders;

use App\Models\ProfileBkGallery;
use Illuminate\Database\Seeder;

class ProfileBkGallerySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title'       => 'Sesi Konseling Individual',
                'description' => 'Pendampingan personal antara siswa dan guru BK untuk membahas kebutuhan akademik, sosial, maupun pribadi.',
                'img'         => null,
                'sort_order'  => 1,
            ],
            [
                'title'       => 'Konseling Kelompok Kelas',
                'description' => 'Diskusi kelompok terarah untuk membangun komunikasi sehat dan dukungan antarsiswa.',
                'img'         => null,
                'sort_order'  => 2,
            ],
            [
                'title'       => 'Workshop Motivasi Belajar',
                'description' => 'Kegiatan penguatan semangat belajar melalui materi motivasi, refleksi, dan strategi belajar efektif.',
                'img'         => null,
                'sort_order'  => 3,
            ],
            [
                'title'       => 'Layanan Informasi Karier',
                'description' => 'Siswa mendapatkan wawasan tentang jurusan, dunia kerja, dan perencanaan karier sejak dini.',
                'img'         => null,
                'sort_order'  => 4,
            ],
            [
                'title'       => 'Pendampingan Masalah Sosial',
                'description' => 'Pendampingan siswa dalam menghadapi konflik pertemanan, adaptasi lingkungan, dan komunikasi.',
                'img'         => null,
                'sort_order'  => 5,
            ],
            [
                'title'       => 'Kegiatan Literasi Mental Health',
                'description' => 'Edukasi kesehatan mental untuk membantu siswa mengenal emosi, stres, dan cara mengelolanya.',
                'img'         => null,
                'sort_order'  => 6,
            ],
            [
                'title'       => 'Sosialisasi Anti Bullying',
                'description' => 'Program pencegahan perundungan melalui kampanye empati, keberanian melapor, dan budaya saling menghargai.',
                'img'         => null,
                'sort_order'  => 7,
            ],
            [
                'title'       => 'Kolaborasi Orang Tua dan BK',
                'description' => 'Sinergi orang tua dan guru BK dalam memantau perkembangan siswa secara berkelanjutan.',
                'img'         => null,
                'sort_order'  => 8,
            ],
        ];

        foreach ($items as $item) {
            ProfileBkGallery::updateOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}
