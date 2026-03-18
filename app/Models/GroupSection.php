<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupSection extends Model
{
    protected $table = 'classroom_groups';

    protected $fillable = ['classroom_id', 'name', 'absen_from', 'absen_to', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Check whether a given absen number falls within this group's range.
        * Null range means the group is open to all members (program classrooms).
     */
    public function containsAbsen(?int $absen): bool
    {
        if (is_null($this->absen_from) && is_null($this->absen_to)) return true;
        if (is_null($absen)) return false;
        return $absen >= $this->absen_from && $absen <= $this->absen_to;
    }

    /** True if this group is open to all members (no absen range). */
    public function getIsOpenGroupAttribute(): bool
    {
        return is_null($this->absen_from) && is_null($this->absen_to);
    }
}
