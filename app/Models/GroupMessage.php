<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;


class GroupMessage extends Model
{
    protected $table = 'group_messages';

    protected $fillable = [
        'classroom_id',
        'user_id',
        'user_type',
        'message',
        'message_type',
        'attachment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function sender(): MorphTo
    {
        return $this->morphTo('sender', 'user_type', 'user_id');
    }
}
