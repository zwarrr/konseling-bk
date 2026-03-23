<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ProfileBkSection extends Model
{
    protected $table = 'profile_bk_section';

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'vision',
        'mission',
        'img',
    ];

    public static function singleton(): static
    {
        if (!Schema::hasTable('profile_bk_section')) {
            return new static([
                'title'       => 'Profil BK',
                'subtitle'    => 'Bimbingan & Konseling',
                'description' => 'Layanan BK membantu peserta didik mengembangkan potensi diri pada aspek pribadi, sosial, belajar, dan karier.',
                'vision'      => null,
                'mission'     => null,
                'img'         => 'assets/img/ilustrasi_profil_bk.png',
            ]);
        }

        return static::firstOrCreate([], [
            'title'       => 'Profil BK',
            'subtitle'    => 'Bimbingan & Konseling',
            'description' => 'Layanan BK membantu peserta didik mengembangkan potensi diri pada aspek pribadi, sosial, belajar, dan karier. Mengacu Permendikbud No. 111 Tahun 2014, BK dilakukan secara sistematis dan berkelanjutan oleh konselor/guru BK untuk memfasilitasi kemandirian, pengambilan keputusan yang tepat, serta penyesuaian diri di lingkungan sekolah, keluarga, dan masyarakat.',
            'vision'      => 'Terwujudnya layanan BK yang ramah, profesional, dan berdampak untuk mendukung perkembangan akademik, sosial, serta karakter siswa.',
            'mission'     => "Menyediakan layanan konseling yang aman dan mudah diakses.\nMendampingi siswa dalam pengembangan diri dan keterampilan sosial.\nMendukung perencanaan studi dan karier melalui bimbingan berkelanjutan.\nMembangun kolaborasi dengan wali kelas, orang tua, dan pihak sekolah.",
            'img'         => 'assets/img/ilustrasi_profil_bk.png',
        ]);
    }
}
