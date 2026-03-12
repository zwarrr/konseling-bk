<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaSection extends Model
{
    protected $table = 'agenda_section';

    protected $fillable = ['title', 'description'];

    public static function singleton(): static
    {
        return static::firstOrCreate([], [
            'title'       => 'Kegiatan & Program Terbaru',
            'description' => 'Informasi kegiatan bimbingan dan konseling yang sedang dan akan datang untuk mendukung perkembangan siswa.',
        ]);
    }
}
