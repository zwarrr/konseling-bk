<?php

namespace App\Http\Controllers;

use App\Events\ChatPresenceUpdated;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function ping(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $now = CarbonImmutable::now();
        $user->forceFill(['last_seen_at' => $now])->save();

        $roomName = (string) $request->input('room_name', '');

        // For siswa, room is always their own account ID.
        if (($user->role ?? null) === 'siswa') {
            $roomName = (string) ($user->account_id_nip_nis ?? $roomName);
        }

        // For guru, require a room_name to broadcast to (otherwise no-op broadcast).
        if ($roomName !== '') {
            broadcast(new ChatPresenceUpdated(
                $roomName,
                (string) ($user->role ?? 'guru'),
                $now->toIso8601String(),
            ))->toOthers();
        }

        return response()->json([
            'success' => true,
            'last_seen_at' => $now->toIso8601String(),
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $roomName = (string) $request->query('room_name', '');
        if ($roomName === '') {
            return response()->json([
                'success' => false,
                'error' => 'room_name is required',
            ], 422);
        }

        $contact = null;
        if (($user->role ?? null) === 'guru') {
            // Contact is siswa by ID = room name
            $contact = User::where('role', 'siswa')
                ->where('account_id_nip_nis', (int) $roomName)
                ->first();
        } else {
            // Contact is BK
            $contact = User::where('role', 'guru')->first();
        }

        $lastSeenAt = $contact?->last_seen_at;
        $lastSeenIso = $lastSeenAt ? $lastSeenAt->toIso8601String() : null;

        $now = CarbonImmutable::now();
        $isOnline = false;
        if ($lastSeenAt) {
            $isOnline = $lastSeenAt->greaterThanOrEqualTo($now->subMinutes(5));
        }

        return response()->json([
            'success' => true,
            'contact_role' => $contact?->role,
            'is_online' => $isOnline,
            'last_seen_at' => $lastSeenIso,
        ]);
    }
}
