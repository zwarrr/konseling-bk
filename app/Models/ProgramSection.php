<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramSection extends Model
{
    protected $table = 'program_section';

    protected $fillable = ['title', 'description'];

    public static function singleton(): static
    {
        return static::firstOrCreate([], [
            'title'       => 'Kegiatan & Program Terbaru',
            'description' => 'Informasi kegiatan bimbingan dan konseling yang sedang dan akan datang untuk mendukung perkembangan siswa.',
        ]);
    }
}
