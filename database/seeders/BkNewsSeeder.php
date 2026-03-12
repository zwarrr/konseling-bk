<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BkNews;

class BkNewsSeeder extends Seeder
{
    public function run(): void
    {
        if (BkNews::count() > 0) return;

        $items = [
            [
                'title'       => 'Layanan BK Online BIKASI',
                'slug'        => 'layanan-bk-online-bikasi',
                'description' => 'Nikmati kemudahan layanan bimbingan konseling secara online, kapan saja dan di mana saja melalui platform BIKASI.',
                'author'      => 'Tim BK BIKASI',
                'img_card'    => 'https://images.unsplash.com/photo-1573497620053-ea5300f94f21?w=1200&q=80',
                'img_cards'   => 'https://images.unsplash.com/photo-1573497620053-ea5300f94f21?w=600&q=80',
                'status'      => 'publish',
            ],
            [
                'title'       => 'Konseling Profesional & Terpercaya',
                'slug'        => 'konseling-profesional-terpercaya',
                'description' => 'Didampingi oleh konselor bersertifikat yang siap membantu mengatasi berbagai tantangan yang dihadapi siswa.',
                'author'      => 'Tim BK BIKASI',
                'img_card'    => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=1200&q=80',
                'img_cards'   => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=600&q=80',
                'status'      => 'publish',
            ],
            [
                'title'       => 'Tumbuh Bersama BIKASI',
                'slug'        => 'tumbuh-bersama-bikasi',
                'description' => 'Program bimbingan konseling yang dirancang untuk mendukung tumbuh kembang siswa secara holistik dan berkesinambungan.',
                'author'      => 'Tim BK BIKASI',
                'img_card'    => 'https://images.unsplash.com/photo-1529390079861-591de354faf5?w=1200&q=80',
                'img_cards'   => 'https://images.unsplash.com/photo-1529390079861-591de354faf5?w=600&q=80',
                'status'      => 'publish',
            ],
        ];

        foreach ($items as $item) {
            BkNews::create($item);
        }
    }
}
