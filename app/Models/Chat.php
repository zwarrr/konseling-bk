<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';

    protected $fillable = [
        'room_id',
        'siswa_account_id',
        'guru_account_id',
        'sender_account_id',
        'sender_role',
        'message',
        'message_type',
        'attachment',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Mark this message as read (blue tick). */
    public function markAsRead(): void
    {
        if ($this->status !== 'read') {
            $this->update(['status' => 'read']);
        }
    }

    /**
     * Mark all messages in a room that were sent by others as read.
     *
     * @param  string  $roomId
     * @param  string  $readerAccountId  account_id of the reader (e.g. SSWAI01)
     */
    public static function markRoomRead(string $roomId, string $readerAccountId): void
    {
        static::where('room_id', $roomId)
              ->where('sender_account_id', '!=', $readerAccountId)
              ->where('status', 'unread')
              ->update(['status' => 'read']);
    }

    /** Is this message unread? */
    public function isUnread(): bool
    {
        return $this->status === 'unread';
    }
}
