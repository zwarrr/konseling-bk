<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Chat $chat)
    {
        // Ensure relations / casts are ready if needed
    }

    public static function roomChannelKey(string $roomName): string
    {
        return rtrim(strtr(base64_encode($roomName), '+/', '-_'), '=');
    }

    public function broadcastOn(): array
    {
        $canonicalRoom = $this->chat->room_name ?: ($this->chat->phone_number ?: 'General');
        $roomKey = self::roomChannelKey($canonicalRoom);

        return [
            new Channel('chat-room.' . $roomKey),
            new Channel('chat-sessions'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message.created';
    }

    public function broadcastWith(): array
    {
        $senderRole = $this->chat->sender_role;
        if (!$senderRole) {
            $senderRole = $this->chat->direction === 'outgoing' ? 'guru' : 'siswa';
        }

        $canonicalRoom = $this->chat->room_name ?: ($this->chat->phone_number ?: 'General');

        return [
            'room_name' => $canonicalRoom,
            'phone_number' => $this->chat->phone_number,
            'message' => [
                'id' => $this->chat->id,
                'sender' => $this->chat->sender_name,
                'sender_role' => $senderRole,
                'message' => $this->chat->message,
                'direction' => $this->chat->direction,
                'message_type' => $this->chat->message_type,
                'status' => $this->chat->status,
                'created_at' => optional($this->chat->created_at)->toIso8601String(),
                'timestamp' => optional($this->chat->created_at)->format('H:i'),
            ],
        ];
    }
}
