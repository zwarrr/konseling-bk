<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BkNews;

class BkNewsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title'       => 'Layanan BK Online E-Konseling',
                'slug'        => 'layanan-bk-online-e-konseling',
                'description' => 'Nikmati kemudahan layanan bimbingan konseling secara online, kapan saja dan di mana saja melalui platform E-Konseling.',
                'author'      => 'Tim BK E-Konseling',
                'img_card'    => null,
                'img_cards'   => null,
            ],
            [
                'title'       => 'Konseling Profesional & Terpercaya',
                'slug'        => 'konseling-profesional-terpercaya',
                'description' => 'Didampingi oleh konselor bersertifikat yang siap membantu mengatasi berbagai tantangan yang dihadapi siswa.',
                'author'      => 'Tim BK E-Konseling',
                'img_card'    => null,
                'img_cards'   => null,
            ],
            [
                'title'       => 'Tumbuh Bersama E-Konseling',
                'slug'        => 'tumbuh-bersama-e-konseling',
                'description' => 'Program bimbingan konseling yang dirancang untuk mendukung tumbuh kembang siswa secara holistik dan berkesinambungan.',
                'author'      => 'Tim BK E-Konseling',
                'img_card'    => null,
                'img_cards'   => null,
            ],
            [
                'title'       => 'Tips Manajemen Waktu untuk Siswa',
                'slug'        => 'tips-manajemen-waktu-untuk-siswa',
                'description' => 'Pelajari langkah sederhana mengatur jadwal belajar, istirahat, dan aktivitas harian agar lebih seimbang dan produktif.',
                'author'      => 'Tim BK E-Konseling',
                'img_card'    => null,
                'img_cards'   => null,
            ],
            [
                'title'       => 'Mengenal Stres Akademik dan Cara Mengatasinya',
                'slug'        => 'mengenal-stres-akademik-dan-cara-mengatasinya',
                'description' => 'Kenali tanda stres akademik sejak dini serta strategi praktis untuk menjaga fokus belajar dan kesehatan mental.',
                'author'      => 'Tim BK E-Konseling',
                'img_card'    => null,
                'img_cards'   => null,
            ],
            [
                'title'       => 'Pentingnya Komunikasi Positif di Sekolah',
                'slug'        => 'pentingnya-komunikasi-positif-di-sekolah',
                'description' => 'Komunikasi yang sehat membantu membangun relasi yang baik dengan teman, guru, dan lingkungan belajar.',
                'author'      => 'Tim BK E-Konseling',
                'img_card'    => null,
                'img_cards'   => null,
            ],
            [
                'title'       => 'Persiapan Karier Sejak Masa Sekolah',
                'slug'        => 'persiapan-karier-sejak-masa-sekolah',
                'description' => 'Mulai petakan minat, bakat, dan peluang karier dari sekarang untuk mempermudah langkah setelah lulus.',
                'author'      => 'Tim BK E-Konseling',
                'img_card'    => null,
                'img_cards'   => null,
            ],
            [
                'title'       => 'Etika Bermedia Sosial bagi Pelajar',
                'slug'        => 'etika-bermedia-sosial-bagi-pelajar',
                'description' => 'Gunakan media sosial secara bijak, aman, dan bertanggung jawab untuk menjaga jejak digital yang positif.',
                'author'      => 'Tim BK E-Konseling',
                'img_card'    => null,
                'img_cards'   => null,
            ],
        ];

        foreach ($items as $item) {
            BkNews::updateOrCreate(['slug' => $item['slug']], $item);
        }

        BkNews::query()->update([
            'img_card' => null,
            'img_cards' => null,
        ]);
    }
}
