<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHiddenRoom extends Model
{
    public $timestamps = false;

    protected $table = 'user_hidden_rooms';

    protected $fillable = ['user_id', 'room_key', 'hidden_at'];

    protected $casts = [
        'hidden_at' => 'datetime',
    ];

    /**
     * Hide a room for a single user (upsert — safe to call multiple times).
     */
    public static function hide(int $userId, string $roomKey): void
    {
        static::firstOrCreate(
            ['user_id' => $userId, 'room_key' => $roomKey],
            ['hidden_at' => now()]
        );
    }

    /**
     * Return a flat array of room_keys hidden by the given user.
     */
    public static function hiddenKeysForUser(int $userId): array
    {
        return static::where('user_id', $userId)->pluck('room_key')->all();
    }
}
