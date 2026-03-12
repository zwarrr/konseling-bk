<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',   // 'bk' | 'siswa'
        'endpoint',
        'endpoint_hash',
        'p256dh',
        'auth',
    ];

    /** Resolve the owning user across guards. */
    public function userModel(): ?\Illuminate\Database\Eloquent\Model
    {
        if ($this->user_type === 'bk') {
            return \App\Models\BkAccount::find($this->user_id);
        }
        return \App\Models\SiswaAccount::find($this->user_id);
    }
}
