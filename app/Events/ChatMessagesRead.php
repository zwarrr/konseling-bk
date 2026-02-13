<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int,int>  $messageIds
     */
    public function __construct(public string $roomName, public array $messageIds)
    {
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
            new Channel('chat-sessions'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.messages.read';
    }

    public function broadcastWith(): array
    {
        return [
            'room_name' => $this->roomName,
            'message_ids' => array_values($this->messageIds),
        ];
    }
}
