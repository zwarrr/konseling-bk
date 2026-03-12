<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',   // 'bk' | 'siswa'
        'type',        // 'agenda' | 'news'
        'title',
        'body',
        'related_id',
        'related_type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /** Resolve the owning user across guards. */
    public function userModel(): ?\Illuminate\Database\Eloquent\Model
    {
        if ($this->user_type === 'bk') {
            return \App\Models\BkAccount::find($this->user_id);
        }
        return \App\Models\SiswaAccount::find($this->user_id);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
