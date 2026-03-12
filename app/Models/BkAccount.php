<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class BkAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'bk_account';

    protected $fillable = [
        'account_id',
        'login_id',
        'name',
        'email',
        'about',
        'profile_photo',
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
        ];
    }

    /** Virtual role attribute — always 'guru' for backward-compatibility. */
    public function getRoleAttribute(): string
    {
        return 'guru';
    }

    /**
     * Auto-generate account_id on creation (prefix: BK).
     * E.g. BK01, BK02, …
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $user) {
            if (!empty($user->account_id)) return;

            $prefix = 'BK';
            $last   = static::where('account_id', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTRING(account_id, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
                ->value('account_id');

            $next             = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
            $user->account_id = $prefix . str_pad($next, 2, '0', STR_PAD_LEFT);
        });
    }

    /** Students assigned to this BK teacher by admin. */
    public function siswa(): HasMany
    {
        return $this->hasMany(SiswaAccount::class, 'bk_id');
    }

    /** Classrooms where this BK is the supervisor (linked by account_id string). */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'bk_account_id', 'account_id');
    }

    /** Master Kelas entries where this BK is assigned as pembimbing. */
    public function masterKelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'bk_id');
    }

    /** Notifications. */
    public function userNotifs(): HasMany
    {
        return $this->hasMany(\App\Models\Users\UserNotification::class, 'user_id')
            ->where('user_type', 'bk');
    }

    /** Push subscriptions. */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\Users\PushSubscription::class, 'user_id')
            ->where('user_type', 'bk');
    }
}
