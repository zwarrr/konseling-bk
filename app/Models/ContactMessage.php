<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'topic',
        'message',
        'status',
        'replied_at',
        'replied_by',
        'reply_subject',
        'reply_message',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];
}
