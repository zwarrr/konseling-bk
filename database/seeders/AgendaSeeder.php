<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agenda;

class AgendaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'slug'            => 'bimbingan-karier-kelas-xii',
                'img'             => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&q=80',
                'img_detail_1'    => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1280&q=80',
                'img_detail_2'    => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=1280&q=80',
                'category'        => 'Konseling',
                'date'            => '2025-03-10',
                'title'           => 'Bimbingan Karier Kelas XII',
                'description'     => 'Sesi bimbingan karier untuk membantu siswa kelas XII mempersiapkan pilihan perguruan tinggi dan karier masa depan.',
                'status'          => 'publish',
                'peserta'         => 0,
                'guru_pembimbing' => 'Guru BK',
                'added_by'        => null,
            ],
            [
                'slug'            => 'seminar-kesehatan-mental',
                'img'             => 'https://images.unsplash.com/photo-1593113630400-ea4288922559?w=800&q=80',
                'img_detail_1'    => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1280&q=80',
                'img_detail_2'    => 'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?w=1280&q=80',
                'category'        => 'Seminar',
                'date'            => '2025-03-15',
                'title'           => 'Seminar Kesehatan Mental',
                'description'     => 'Seminar tentang pentingnya menjaga kesehatan mental bagi pelajar di era digital yang penuh tantangan.',
                'status'          => 'publish',
                'peserta'         => 0,
                'guru_pembimbing' => 'Guru BK',
                'added_by'        => null,
            ],
            [
                'slug'            => 'workshop-manajemen-stres',
                'img'             => 'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?w=800&q=80',
                'img_detail_1'    => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1280&q=80',
                'img_detail_2'    => null,
                'category'        => 'Workshop',
                'date'            => '2025-03-20',
                'title'           => 'Workshop Manajemen Stres',
                'description'     => 'Pelatihan teknik manajemen stres untuk membantu siswa menghadapi tekanan belajar dan ujian nasional.',
                'status'          => 'publish',
                'peserta'         => 0,
                'guru_pembimbing' => 'Guru BK',
                'added_by'        => null,
            ],
            [
                'slug'            => 'forum-perencanaan-masa-depan',
                'img'             => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&q=80',
                'img_detail_1'    => 'https://images.unsplash.com/photo-1529390079861-591de354faf5?w=1280&q=80',
                'img_detail_2'    => null,
                'category'        => 'Diskusi',
                'date'            => '2025-03-25',
                'title'           => 'Forum Perencanaan Masa Depan',
                'description'     => 'Forum terbuka untuk siswa berdiskusi dan berbagi pengalaman tentang rencana studi dan karier masa depan.',
                'status'          => 'publish',
                'peserta'         => 0,
                'guru_pembimbing' => 'Guru BK',
                'added_by'        => null,
            ],
            [
                'slug'            => 'pelatihan-kecerdasan-emosional',
                'img'             => 'https://images.unsplash.com/photo-1529390079861-591de354faf5?w=800&q=80',
                'img_detail_1'    => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1280&q=80',
                'img_detail_2'    => 'https://images.unsplash.com/photo-1593113630400-ea4288922559?w=1280&q=80',
                'category'        => 'Pelatihan',
                'date'            => '2025-04-05',
                'title'           => 'Pelatihan Kecerdasan Emosional',
                'description'     => 'Pelatihan untuk meningkatkan kecerdasan emosional siswa agar mampu menghadapi tantangan sosial dan akademik dengan lebih baik.',
                'status'          => 'publish',
                'peserta'         => 0,
                'guru_pembimbing' => 'Guru BK',
                'added_by'        => null,
            ],
        ];

        foreach ($items as $item) {
            Agenda::create($item);
        }
    }
}
