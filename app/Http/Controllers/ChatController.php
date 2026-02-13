<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageCreated;
use App\Events\ChatMessagesRead;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display the main chat page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('auth.login');
        }

        return ($user->role ?? null) === 'guru'
            ? redirect()->route('bk.chat')
            : redirect()->route('siswa.chat');
    }

    public function bkChat(Request $request)
    {
        abort_unless(($request->user()->role ?? null) === 'guru', 403);
        return view('chat.chat_section', [
            'roomBasePath' => '/bk/room',
        ]);
    }

    public function siswaChat(Request $request)
    {
        abort_unless(($request->user()->role ?? null) === 'siswa', 403);
        return view('chat.chat_section', [
            'roomBasePath' => '/siswa/room',
        ]);
    }

    /**
     * Display a specific chat room
     */
    public function room(Request $request, $roomId = null)
    {
        // Support old parameter name 'sesi_id'
        $roomId = $roomId ?? $request->query('sesi_id');

        // Enforce role-specific room URLs
        $path = '/' . ltrim($request->path(), '/');
        $role = $request->user()->role ?? null;

        // If user is authenticated but visits the wrong role prefix, redirect to the correct one.
        if (str_starts_with($path, '/bk/') && $role === 'siswa') {
            return redirect()->route('siswa.room', ['roomId' => $roomId] + $request->query());
        }
        if (str_starts_with($path, '/siswa/') && $role === 'guru') {
            return redirect()->route('bk.room', ['roomId' => $roomId] + $request->query());
        }

        if (str_starts_with($path, '/bk/') && $role !== 'guru') {
            abort(403);
        }
        if (str_starts_with($path, '/siswa/') && $role !== 'siswa') {
            abort(403);
        }

        $roomName = $roomId ?? 'General';
        $isEmbed = $request->query('embed', false);

        $backUrl = $role === 'guru' ? route('bk.chat') : route('siswa.chat');
        
        // Messages will be loaded via AJAX from API
        // No need to query here - JavaScript loadMessages() handles it
        return view('chat.room_chat_section', compact('roomName', 'isEmbed', 'backUrl'));
    }

    /**
     * Send a message (API endpoint)
     */
    public function sendMessage(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'phone_number' => 'required|string',
            'room_name' => 'nullable|string',
            'sender_name' => 'nullable|string|max:255',
            'sender_role' => 'nullable|in:siswa,guru',
        ]);

        // Always derive sender identity from authenticated user.
        $effectiveRole = ($user && ($user->role ?? null) === 'siswa') ? 'siswa' : 'guru';
        $effectiveName = $user?->name ?: ($validated['sender_name'] ?? 'Anonymous');

        // Ensure room_name is always present so broadcasting keys are consistent.
        if (empty($validated['room_name'])) {
            $validated['room_name'] = $validated['phone_number'] ?? 'General';
        }

        // If siswa is logged in, force the room and sender_role to their own account ID.
        if ($user && ($user->role ?? null) === 'siswa') {
            $validated['phone_number'] = (string) $user->account_id_nip_nis;
            $validated['room_name'] = (string) $user->account_id_nip_nis;
            $validated['sender_role'] = 'siswa';
        }

        $senderRole = $effectiveRole;
        // Direction is stored relative to guru (BK):
        // - incoming: message from siswa -> guru
        // - outgoing: message from guru -> siswa
        $direction = $senderRole === 'siswa' ? 'incoming' : 'outgoing';

        // Simpan ke database (mode local/offline: tidak mengirim ke layanan eksternal)
        $chat = Chat::create([
            'sender_name' => $effectiveName,
            'sender_role' => $senderRole,
            'phone_number' => $validated['phone_number'],
            'room_name' => $validated['room_name'] ?? 'General',
            'direction' => $direction,
            'message' => $validated['message'],
            'message_type' => 'text',
            // UI expects delivered for double-check; offline mode marks as delivered immediately.
            'status' => 'delivered'
        ]);

        // Broadcast realtime update (Soketi / Pusher protocol)
        broadcast(new ChatMessageCreated($chat))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message stored successfully',
            'data' => $chat->fresh()
        ]);
    }

    /**
     * Get messages for a room (API endpoint)
        * Mengambil pesan dari database berdasarkan room_name
     */
    public function getMessages(Request $request)
    {
        $user = $request->user();

        $roomName = $request->query('room_name');
        $viewerRole = $request->query('viewer_role', 'guru');
        if (!in_array($viewerRole, ['siswa', 'guru'], true)) {
            $viewerRole = 'guru';
        }

        // Lock down access for siswa: only their own room.
        if ($user && ($user->role ?? null) === 'siswa') {
            $roomName = (string) $user->account_id_nip_nis;
            $viewerRole = 'siswa';
        }

        // Force viewer role for guru as well.
        if ($user && ($user->role ?? null) === 'guru') {
            $viewerRole = 'guru';
        }
        if (!$roomName) {
            return response()->json([
                'success' => false,
                'error' => 'room_name is required',
                'data' => []
            ], 422);
        }

        // Mark messages as read when recipient opens the room.
        // Direction is stored relative to guru (BK):
        // - incoming: siswa -> guru  (recipient: guru)
        // - outgoing: guru -> siswa  (recipient: siswa)
        $directionToMarkRead = $viewerRole === 'guru' ? 'incoming' : 'outgoing';
        $idsToMarkRead = Chat::where(function ($q) use ($roomName) {
                $q->where('room_name', $roomName)
                  ->orWhere('phone_number', $roomName);
            })
            ->where('direction', $directionToMarkRead)
            ->where(function ($q) {
                $q->where('is_read', false)
                  ->orWhere('status', '!=', 'read');
            })
            ->pluck('id')
            ->all();

        if (!empty($idsToMarkRead)) {
            Chat::whereIn('id', $idsToMarkRead)->update([
                'is_read' => true,
                'status' => 'read',
            ]);

            // Notify the other side so their UI can turn double-check blue.
            broadcast(new ChatMessagesRead($roomName, $idsToMarkRead))->toOthers();
        }
        
        // Get messages from database - match by room_name/phone_number
        $rawMessages = Chat::where(function($q) use ($roomName) {
                $q->where('room_name', $roomName)
                  ->orWhere('phone_number', $roomName);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Dedupe messages for UI: if a message exists in multiple delivery states,
        // keep the most "final" one (read > delivered > sent > pending > failed).
        $statusRank = [
            'failed' => -1,
            'pending' => 0,
            'sent' => 1,
            'delivered' => 2,
            'read' => 3,
        ];

        $bestByKey = [];
        foreach ($rawMessages as $chat) {
            // In local-only mode (no WA/WACEN), `wa_message_id` is typically null.
            // Using a content-based key can accidentally collapse distinct messages
            // (e.g. same text within the same minute). Use row id as fallback.
            $key = $chat->wa_message_id
                ? ('ext:' . $chat->wa_message_id)
                : ('id:' . $chat->id);

            $currentRank = $statusRank[$chat->status] ?? 0;
            if (!isset($bestByKey[$key])) {
                $bestByKey[$key] = ['rank' => $currentRank, 'chat' => $chat];
                continue;
            }

            if ($currentRank > $bestByKey[$key]['rank']) {
                $bestByKey[$key] = ['rank' => $currentRank, 'chat' => $chat];
            }
        }

        $messages = collect(array_values($bestByKey))
            ->map(fn($v) => $v['chat'])
            ->sortBy('created_at')
            ->values()
            ->map(function($chat) use ($viewerRole) {
                $senderRole = $chat->sender_role;
                if (!$senderRole) {
                    $senderRole = $chat->direction === 'outgoing' ? 'guru' : 'siswa';
                }
                return [
                    'id' => $chat->id,
                    'wa_message_id' => $chat->wa_message_id,
                    'sender' => $chat->sender_name,
                    'sender_role' => $senderRole,
                    'phone' => $chat->phone_number,
                    'message' => $chat->message,
                    'direction' => $chat->direction,
                    'message_type' => $chat->message_type,
                    'status' => $chat->status,
                    'timestamp' => $chat->created_at->format('H:i'),
                    'created_at' => $chat->created_at->toIso8601String(),
                    'is_self' => $senderRole === $viewerRole,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $messages,
            'room' => $roomName,
            'total' => $messages->count()
        ]);
    }

    /**
     * Get list of chat sessions/rooms (API endpoint)
        * Mengambil daftar sesi/room dari database
     */
    public function getSessions(Request $request)
    {
        $user = $request->user();

        // Siswa: always only 1 contact (BK)
        if ($user && ($user->role ?? null) === 'siswa') {
                                                $room = (string) ($user->account_id_nip_nis ?? '');
            $latest = Chat::where(function ($q) use ($room) {
                    $q->where('room_name', $room)
                      ->orWhere('phone_number', $room);
                })
                ->orderByDesc('created_at')
                ->first();

            $unread = (int) Chat::where(function ($q) use ($room) {
                $q->where('room_name', $room)
                  ->orWhere('phone_number', $room);
            })
            // For siswa, unread are messages from guru -> siswa (direction=outgoing)
            ->where('direction', 'outgoing')
            ->where('is_read', false)
            ->count();

            $bkName = User::where('role', 'guru')->value('name') ?: 'Guru BK';

            return response()->json([
                'success' => true,
                'data' => [[
                    'id' => $room,
                    'room_name' => $room,
                    'phone_number' => $room,
                    'contact_name' => $bkName,
                    'last_message' => $latest?->message ?? '',
                    'last_message_status' => $latest?->status ?? null,
                    'last_activity' => $latest?->created_at?->toIso8601String() ?? now()->toIso8601String(),
                    'unread_count' => $unread,
                    'status' => 'active',
                    'direction' => ($latest?->direction ?? 'incoming') === 'outgoing' ? 'out' : 'in',
                    'assigned_user' => null,
                    'avatar' => 'https://img.icons8.com/?size=96&id=Z1n6MSkfdSgd&format=png&color=9E9E9E',
                ]],
                'total' => 1,
            ]);
        }

        $status = $request->query('status');
        // Treat status=all as no filter (fetch all statuses)
        if ($status === 'all') {
            $status = null;
        }

        $sort = strtolower((string) $request->query('sort', 'desc')) === 'asc' ? 'asc' : 'desc';
        $limit = min((int) $request->query('limit', 50), 200);
        $search = $request->query('search');

        // Canonical room key: prefer room_name, fallback to phone_number (for older records)
        $roomKeyExpr = "COALESCE(NULLIF(room_name, ''), phone_number)";

        $latestIdsByRoom = Chat::query()
            ->selectRaw($roomKeyExpr . ' as room_key, MAX(id) as last_id')
            ->where(function ($q) {
                $q->whereNotNull('room_name')->orWhereNotNull('phone_number');
            })
            ->groupBy('room_key');

        $unreadByRoom = Chat::query()
            ->selectRaw($roomKeyExpr . " as room_key, SUM(CASE WHEN direction = 'incoming' AND is_read = 0 THEN 1 ELSE 0 END) as unread_count")
            ->where(function ($q) {
                $q->whereNotNull('room_name')->orWhereNotNull('phone_number');
            })
            ->groupBy('room_key');

        $query = Chat::query()
            ->joinSub($latestIdsByRoom, 'latest', function ($join) {
                $join->on('chats.id', '=', 'latest.last_id');
            })
            ->leftJoinSub($unreadByRoom, 'unread', function ($join) {
                $join->on('latest.room_key', '=', 'unread.room_key');
            })
            ->select([
                'latest.room_key as room_key',
                'chats.room_name',
                'chats.phone_number',
                'chats.message',
                'chats.direction',
                'chats.status',
                'chats.created_at',
                'unread.unread_count',
            ])
            ->orderBy('chats.created_at', $sort);

        if ($user && ($user->role ?? null) === 'siswa') {
                        $roomKey = (string) $user->account_id_nip_nis;
                        $query->where(function ($q) use ($roomKey) {
                                $q->where('chats.room_name', $roomKey)
                                    ->orWhere('chats.phone_number', $roomKey);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('chats.room_name', 'like', '%' . $search . '%')
                  ->orWhere('chats.phone_number', 'like', '%' . $search . '%')
                  ->orWhere('chats.message', 'like', '%' . $search . '%');
            });
        }

        $rows = $query->limit($limit)->get();

        // Batch-load siswa names for contact display
        $accountIdCandidates = collect($rows)
            ->flatMap(function ($row) {
                return [
                    (string) ($row->room_name ?? ''),
                    (string) ($row->phone_number ?? ''),
                ];
            })
            ->filter(fn ($v) => $v !== '' && preg_match('/^\d+$/', $v))
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values();

        $siswaNamesByAccountId = User::query()
            ->where('role', 'siswa')
            ->whereIn('account_id_nip_nis', $accountIdCandidates)
            ->get(['account_id_nip_nis', 'name'])
            ->mapWithKeys(fn ($u) => [(int) $u->account_id_nip_nis => (string) $u->name])
            ->all();

        $sessions = $rows->map(function ($row) use ($siswaNamesByAccountId) {
            $roomKey = (string) ($row->room_key ?? ($row->room_name ?? $row->phone_number ?? ''));
            $roomId = $row->room_name ?? $row->phone_number ?? $roomKey;

            $contactName = null;
            $roomAccountId = is_numeric($roomId) ? (int) $roomId : (is_numeric($row->room_name) ? (int) $row->room_name : null);
            $phoneAccountId = is_numeric($row->phone_number) ? (int) $row->phone_number : null;

            if ($roomAccountId !== null && isset($siswaNamesByAccountId[$roomAccountId])) {
                $contactName = $siswaNamesByAccountId[$roomAccountId];
            } elseif ($phoneAccountId !== null && isset($siswaNamesByAccountId[$phoneAccountId])) {
                $contactName = $siswaNamesByAccountId[$phoneAccountId];
            }

            return [
                // In local mode, session ID is the room identifier.
                'id' => $roomId,
                'room_name' => $roomId,
                'phone_number' => $row->phone_number ?? $roomId,
                'contact_name' => $contactName ?? $roomId,
                'last_message' => $row->message,
                'last_message_status' => $row->status ?? null,
                'last_activity' => $row->created_at ? $row->created_at->toIso8601String() : now()->toIso8601String(),
                'unread_count' => (int) ($row->unread_count ?? 0),
                'status' => 'active',
                // Keep legacy direction values used by frontend mapping.
                'direction' => $row->direction === 'outgoing' ? 'out' : 'in',
                'assigned_user' => null,
                'avatar' => 'https://img.icons8.com/?size=96&id=Z1n6MSkfdSgd&format=png&color=9E9E9E'
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $sessions,
            'total' => $sessions->count()
        ]);
    }

    /**
     * Handle typing indicator (API endpoint)
     */
    public function typing(Request $request)
    {
        $validated = $request->validate([
            'room_name' => 'required|string',
            'sender_name' => 'required|string',
            'is_typing' => 'required|boolean'
        ]);

        // Broadcast typing status (bisa pakai broadcasting/websocket)
        return response()->json([
            'success' => true,
            'message' => 'Typing status updated'
        ]);
    }

    /**
     * Create new chat session (API endpoint)
     */
    public function newSession(Request $request)
    {
        $validated = $request->validate([
            'room_name' => 'required|string|unique:chats,room_name'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'New session created',
            'data' => [
                'room_name' => $validated['room_name']
            ]
        ]);
    }

    /**
     * Get message status by api_ref
     */
    public function getMessageStatus($apiRef)
    {
        $chat = Chat::where('wa_message_id', $apiRef)
            ->orWhere('id', $apiRef)
            ->first();

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $chat
        ]);
    }

    /**
     * End chat session (API endpoint)
     */
    public function endSession(Request $request)
    {
        $validated = $request->validate([
            'room_name' => 'required|string'
        ]);

        // Bisa mark semua message sebagai archived atau soft delete
        Chat::where('room_name', $validated['room_name'])
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Session ended'
        ]);
    }
}
