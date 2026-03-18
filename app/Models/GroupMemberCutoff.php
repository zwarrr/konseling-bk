<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMemberCutoff extends Model
{
    protected $table = 'classroom_member_cutoffs';

    protected $fillable = [
        'user_id',
        'classroom_id',
        'cutoff_message_id',
        'left_cutoff_message_id',
    ];

    /**
     * Return the cutoff message ID for a given siswa + classroom.
     * Returns 0 when no cutoff is set (show all history — e.g. BK or original members).
     */
    public static function getCutoff(int $userId, string $classroomId): int
    {
        return (int) static::where('user_id', $userId)
            ->where('classroom_id', $classroomId)
            ->value('cutoff_message_id');
    }

    /** Return the leave cutoff message ID (0 when not set). */
    public static function getLeaveCutoff(int $userId, string $classroomId): int
    {
        return (int) static::where('user_id', $userId)
            ->where('classroom_id', $classroomId)
            ->value('left_cutoff_message_id');
    }

    /**
     * Resolve leave cutoff, including legacy fallback based on the user's latest
     * "keluar dari grup ini" system message.
     */
    public static function resolveLeaveCutoff(int $userId, string $classroomId): int
    {
        $stored = static::getLeaveCutoff($userId, $classroomId);
        if ($stored > 0) return $stored;

        $fallback = (int) GroupMessage::where('classroom_id', $classroomId)
            ->where('user_id', $userId)
            ->where('message_type', 'system')
            ->where('message', 'like', '%keluar dari grup ini%')
            ->latest('id')
            ->value('id');

        if ($fallback > 0) {
            static::updateOrCreate(
                ['user_id' => $userId, 'classroom_id' => $classroomId],
                ['left_cutoff_message_id' => $fallback]
            );
        }

        return $fallback;
    }

    /**
     * Record the cutoff at the moment a student joins a classroom.
     * Snapshot the current max message ID — messages at or below this ID
     * will be hidden from the newly-joined student.
     */
    public static function recordJoin(int $userId, string $classroomId): void
    {
        $maxId = (int) GroupMessage::where('classroom_id', $classroomId)->max('id');

        static::updateOrCreate(
            ['user_id' => $userId, 'classroom_id' => $classroomId],
            ['cutoff_message_id' => $maxId, 'left_cutoff_message_id' => null]
        );
    }

    /** Record that a user has left / been removed at a given message ID. */
    public static function recordLeave(int $userId, string $classroomId, int $messageId): void
    {
        if ($messageId <= 0) return;

        static::updateOrCreate(
            ['user_id' => $userId, 'classroom_id' => $classroomId],
            ['left_cutoff_message_id' => $messageId]
        );
    }
}
