<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin_account';

    protected $fillable = [
        'name',
        'account_id',
        'login_id',
        'password',
        'must_change_password',
    ];

    /**
     * Auto-generate account_id on creation (prefix: ADM).
     * E.g. ADM1, ADM2, …
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $user) {
            if (!empty($user->account_id)) return;

            $prefix = 'ADM';
            $last   = static::where('account_id', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTRING(account_id, ?) AS UNSIGNED) DESC', [strlen($prefix) + 1])
                ->value('account_id');

            $next             = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
            $user->account_id = $prefix . $next;
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'             => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /** Virtual role attribute for compatibility with existing role checks. */
    public function getRoleAttribute(): string
    {
        return 'admin';
    }
}
