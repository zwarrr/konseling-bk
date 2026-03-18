<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    protected $table = 'programs';

    protected $fillable = ['slug', 'img', 'img_detail_1', 'img_detail_2', 'category', 'date', 'title', 'description', 'benefits', 'info_link', 'classroom_id', 'status', 'peserta', 'guru_pembimbing', 'added_by'];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    protected $casts = [
        'date'    => 'date',
        'peserta' => 'integer',
        'benefits' => 'array',
    ];

    public function getFormattedDateAttribute(): string
    {
        return $this->date
            ? Carbon::parse($this->date)->translatedFormat('d M Y')
            : '';
    }
}
