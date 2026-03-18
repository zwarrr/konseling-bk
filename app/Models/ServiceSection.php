<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSection extends Model
{
    protected $table = 'service_section';

    protected $fillable = ['title', 'subtitle', 'description', 'img'];

    public static function singleton(): static
    {
        return static::firstOrCreate([], [
            'title'       => 'Kelola Semua Kebutuhan BK',
            'subtitle'    => 'Gunakan E-Konseling untuk',
            'description' => 'Platform digital konseling yang dirancang untuk mendukung perkembangan siswa secara menyeluruh — kapan saja, di mana saja.',
            'img'         => null,
        ]);
    }
}
