<?php

namespace App\Models;

use App\Models\BkAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table    = 'classes';
    protected $fillable = ['kelas', 'jurusan', 'jumlah_siswa_i', 'bk_id'];
    protected $appends  = ['label'];

    protected $casts = [
        'jumlah_siswa_i' => 'integer',
    ];

    /** Full label shown in dropdowns: e.g. "XII RPL" */
    public function getLabelAttribute(): string
    {
        return $this->kelas . ' ' . $this->jurusan;
    }

    /** BK teacher assigned to this master class. */
    public function bk(): BelongsTo
    {
        return $this->belongsTo(BkAccount::class, 'bk_id');
    }

    /** Classroom chat groups that are linked to this master entry. */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'class_id');
    }
}
