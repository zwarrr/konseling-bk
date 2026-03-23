<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\Kelas;
use App\Models\SiswaAccount;



class Classroom extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable  = ['id', 'name', 'description', 'bk_account_id', 'class_id'];

    /** Auto-generate id (KLS01, KLS02, …) and bk_account_id on creation. */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $kelas) {
            // Auto ID
            if (empty($kelas->id)) {
                $last = static::where('id', 'like', 'KLS%')
                    ->orderByRaw('CAST(SUBSTRING(id, 4) AS UNSIGNED) DESC')
                    ->value('id');

                $next      = $last ? ((int) substr($last, 3)) + 1 : 1;
                $kelas->id = 'KLS' . str_pad($next, 2, '0', STR_PAD_LEFT);
            }

            // Auto-set BK account owner only when BK guard is authenticated.
            if (empty($kelas->bk_account_id) && auth('bk')->check()) {
                $kelas->bk_account_id = auth('bk')->user()->account_id;
            }
        });
    }

    /** URL-friendly slug derived from the classroom name (e.g. "XII RPL" → "xii-rpl"). */
    public function getSlugAttribute(): string
    {
        return Str::slug($this->name);
    }

    /** Students belonging to this classroom. */
    public function students(): HasMany
    {
        return $this->hasMany(SiswaAccount::class, 'classroom_id', 'id');
    }

    /** Messages in this classroom's group chat. */
    public function messages(): HasMany
    {
        return $this->hasMany(GroupMessage::class, 'classroom_id', 'id');
    }

    /** Sub-groups (pembagian kelompok) defined by BK. */
    public function groups(): HasMany
    {
        return $this->hasMany(GroupSection::class, 'classroom_id', 'id');
    }

    /** Only groups that have been activated by BK. */
    public function activeGroups(): HasMany
    {
        return $this->hasMany(GroupSection::class, 'classroom_id', 'id')->where('is_active', true);
    }

    /** Class data this classroom is based on. */
    public function dataKelas(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }
}
