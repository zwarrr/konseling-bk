<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSection extends Model
{
    protected $table = 'about_section';

    protected $fillable = [
        'title', 'subtitle', 'description',
        'img_1', 'img_2', 'img_3', 'img_4',
    ];

    public static function singleton(): static
    {
        return static::firstOrCreate([], [
            'title'       => 'Guru BK yang Siap',
            'subtitle'    => 'Melayani Sepenuh Hati',
            'description' => 'Tim konselor E-Konseling terdiri dari guru BK bersertifikat yang berdedikasi mendampingi setiap siswa — mulai dari tantangan akademik, perencanaan karier, hingga kesehatan mental dan kehidupan sosial.',
            'img_1'       => 'https://images.unsplash.com/photo-1529400971008-f566de0e6dfc?w=800&q=80',
            'img_2'       => 'https://images.unsplash.com/photo-1606761568499-6d2451b23c66?w=800&q=80',
            'img_3'       => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80',
            'img_4'       => 'https://images.unsplash.com/photo-1543269865-cbf427effbad?w=800&q=80',
        ]);
    }
}
