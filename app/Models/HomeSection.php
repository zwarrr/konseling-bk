<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $table = 'beranda_section';

    protected $fillable = ['title', 'subtitle', 'description', 'img'];

    /**
     * Get (or create) the single home section record.
     */
    public static function singleton(): static
    {
        return static::firstOrCreate([], [
            'title'       => 'Tumbuh Bersama',
            'subtitle'    => 'Bimbingan & Konseling',
            'description' => 'Platform digital E-Konseling yang menghubungkan siswa dengan guru konselor secara mudah, privat, dan profesional — kapan saja, di mana saja.',
            'img'         => 'assets/img/promot_iphone3d.png',
        ]);
    }
}
