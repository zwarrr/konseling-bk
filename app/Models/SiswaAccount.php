<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SiswaAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'siswa_i_account';

    protected $fillable = [
        'account_id',
        'login_id',
        'name',
        'jenis_kelamin',
        'email',
        'about',
        'profile_photo',
        'bk_id',
        'classroom_id',
        'absen',
        'last_seen_at',
        'password',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at'         => 'datetime',
            'password'             => 'hashed',
            'must_change_password' => 'boolean',
            'jenis_kelamin'        => 'string',
        ];
    }

    /** Virtual role attribute — 'siswa' (also accepted wherever 'user' was used). */
    public function getRoleAttribute(): string
    {
        return 'siswa';
    }

    /**
     * Auto-generate account_id on creation (prefix: SSWA).
     * E.g. SSWA01, SSWA02, …
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $user) {
            if (!empty($user->account_id)) return;

            $prefix = 'SSWA';
            $last   = static::where('account_id', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTRING(account_id, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
                ->value('account_id');

            $next             = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
            $user->account_id = $prefix . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /** The BK teacher assigned to this student by admin. */
    public function bk(): BelongsTo
    {
        return $this->belongsTo(BkAccount::class, 'bk_id');
    }

    /** The classroom group this student belongs to. */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id', 'id');
    }

    /** Notifications. */
    public function userNotifs(): HasMany
    {
        return $this->hasMany(\App\Models\Users\UserNotification::class, 'user_id')
            ->where('user_type', 'siswa');
    }

    /** Push subscriptions. */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\Users\PushSubscription::class, 'user_id')
            ->where('user_type', 'siswa');
    }
}
