<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AboutSection;

class AboutSectionSeeder extends Seeder
{
    public function run(): void
    {
        AboutSection::firstOrCreate([], [
            'title'       => 'Guru BK yang Siap',
            'subtitle'    => 'Melayani Sepenuh Hati',
            'description' => 'Tim konselor BIKASI terdiri dari guru BK bersertifikat yang berdedikasi mendampingi setiap siswa — mulai dari tantangan akademik, perencanaan karier, hingga kesehatan mental dan kehidupan sosial.',
            'img_1'       => 'https://images.unsplash.com/photo-1529400971008-f566de0e6dfc?w=800&q=80',
            'img_2'       => 'https://images.unsplash.com/photo-1606761568499-6d2451b23c66?w=800&q=80',
            'img_3'       => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80',
            'img_4'       => 'https://images.unsplash.com/photo-1543269865-cbf427effbad?w=800&q=80',
        ]);
    }
}
