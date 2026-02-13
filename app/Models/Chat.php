<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'api_ref',
        'wa_message_id',
        'sender_name',
        'sender_role',
        'phone_number',
        'room_name',
        'direction',
        'message',
        'message_type',
        'status',
        'error_message',
        'attachment',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}
