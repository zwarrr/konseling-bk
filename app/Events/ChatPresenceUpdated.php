<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatPresenceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomName,
        public string $userRole,
        public ?string $lastSeenAtIso,
    ) {
    }

    private static function roomChannelKey(string $roomName): string
    {
        return rtrim(strtr(base64_encode($roomName), '+/', '-_'), '=');
    }

    public function broadcastOn(): array
    {
        $roomKey = self::roomChannelKey($this->roomName ?: 'General');

        return [
            new Channel('chat-room.' . $roomKey),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.presence.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'room_name' => $this->roomName,
            'user_role' => $this->userRole,
            'last_seen_at' => $this->lastSeenAtIso,
        ];
    }
}
