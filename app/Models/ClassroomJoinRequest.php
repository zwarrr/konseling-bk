<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SiswaAccount;

class ClassroomJoinRequest extends Model
{
    protected $table    = 'group_join_requests';
    protected $fillable = ['classroom_id', 'user_id', 'status', 'note', 'responded_at'];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────────────

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(SiswaAccount::class, 'user_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForBk($query, string $bkAccountId)
    {
        return $query->whereHas('classroom', fn ($q) => $q->where('bk_account_id', $bkAccountId));
    }
}
