<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BkAccount;
use App\Models\Chat;
use App\Models\Classroom;
use App\Models\GroupMessage;
use App\Models\GroupReadReceipt;
use App\Models\SiswaAccount;
use App\Models\UserHiddenRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserChatController extends Controller
{
    private const SYSTEM_GURU_ACCOUNT_ID = 'EKON';
    private const SYSTEM_NAME = 'E-Konseling';

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Build or retrieve the readable room_id for a siswa↔guru pair.
     *
     * Format: RC + 2-char siswa-name-initials + 2-char guru-name-initials + 2-digit sequence
     * Example: RCSIGURU01
     *
     * Returns the existing room_id if the pair already has messages,
     * otherwise mints a new unique one.
     */
    private function buildRoomId($siswa, $guru): string
    {
        // Return existing room_id for this exact pair
        $existing = Chat::where('siswa_account_id', $siswa->account_id)
                        ->where('guru_account_id',  $guru->account_id)
                        ->value('room_id');
        if ($existing) {
            return $existing;
        }

        // Build prefix from letters only (uppercase, padded to 2 with 'X')
        $si = strtoupper(str_pad(
            substr(preg_replace('/[^a-zA-Z]/', '', $siswa->name), 0, 2),
            2, 'X'
        ));
        $gi = strtoupper(str_pad(
            substr(preg_replace('/[^a-zA-Z]/', '', $guru->name), 0, 2),
            2, 'X'
        ));
        $prefix = 'RC' . $si . $gi;   // e.g. RCSIGR

        // Find first unused 2-digit sequence number
        $seq = 1;
        while (Chat::where('room_id', $prefix . str_pad($seq, 2, '0', STR_PAD_LEFT))->exists()) {
            $seq++;
        }

        return $prefix . str_pad($seq, 2, '0', STR_PAD_LEFT);
    }

    /** Resolve a display name from an account_id (searches BK then siswa). */
    private function nameByAccountId(string $accountId): string
    {
        if ($accountId === self::SYSTEM_GURU_ACCOUNT_ID) {
            return self::SYSTEM_NAME;
        }

        return BkAccount::where('account_id', $accountId)->value('name')
            ?? SiswaAccount::where('account_id', $accountId)->value('name')
            ?? '?';
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    /**
     * For guru: list of 1-on-1 chats.
     */
    public function index(): View|RedirectResponse
    {
        $authUser = auth()->user();
        $role     = $authUser->role ?? 'user';
        $pfx      = $role === 'guru' ? 'bk' : 'siswa';

        $filter = request()->query('filter');

        if ($role !== 'guru') {
            return redirect()->route('siswa.chat');
        }

        // ── Guru: 1-on-1 rooms ─────────────────────────────────────────────
        $roomRows = Chat::where('guru_account_id', $authUser->account_id)
            ->select('room_id', 'siswa_account_id')
            ->distinct()
            ->get();

        $conversations = $roomRows->map(function ($row) use ($authUser) {
            $siswa  = SiswaAccount::where('account_id', $row->siswa_account_id)->first();
            $last   = Chat::where('room_id', $row->room_id)->latest()->first();
            $unread = Chat::where('room_id', $row->room_id)
                ->where('sender_account_id', '!=', $authUser->account_id)
                ->where('status', 'unread')
                ->count();
            return [
                'type'            => 'direct',
                'id'              => $row->room_id,
                'room_key'        => $row->room_id,
                'href'            => route('chat.room', $row->room_id),
                'name'            => $siswa?->name ?? '—',
                'avatar_initial'  => strtoupper(substr($siswa?->name ?? '?', 0, 1)),
                'avatar_color'    => '#6b7280',
                'last_message'      => $last?->message,
                'last_message_type' => $last?->message_type ?? 'text',
                'last_time'         => $last?->created_at,
                'last_is_mine'      => $last ? ($last->sender_account_id === $authUser->account_id) : false,
                'last_status'       => $last?->status,
                'unread'            => $unread,
            ];
        })->sortByDesc(fn($c) => $c['last_time']?->timestamp ?? 0)->values();

        // ── Append kelas (group chat) rooms — one entry per active group ─
        // Pre-fetch program classroom IDs owned by this BK (no activeGroups required)
        $programClassroomIds = \App\Models\Program::whereNotNull('classroom_id')
            ->whereHas('classroom', fn($q) => $q->where('bk_account_id', $authUser->account_id))
            ->pluck('classroom_id');

        $kelasConvs = Classroom::where('bk_account_id', $authUser->account_id)
            ->with('activeGroups')
            ->orderBy('name')->get()->flatMap(function ($kelas) use ($authUser, $programClassroomIds) {
            $last     = GroupMessage::where('classroom_id', $kelas->id)->latest()->first();
            $receipt  = GroupReadReceipt::where('user_id', $authUser->id)
                            ->where('classroom_id', $kelas->id)->first();
            $lastRead = $receipt?->last_read_message_id ?? 0;
            $unread   = GroupMessage::where('classroom_id', $kelas->id)
                            ->where('id', '>', $lastRead)
                            ->where(function($q) use ($authUser) {
                                $q->where('user_id', '!=', (int) $authUser->id)
                                  ->orWhere('user_type', '!=', 'bk');
                            })
                            ->count();
            $lastIsMine = $last && (int)$last->user_id === (int)$authUser->id && $last->user_type === 'bk';
            $lastStatus = null;
            if ($lastIsMine) {
                $chkOther = GroupReadReceipt::where('classroom_id', $kelas->id)
                    ->where('user_id', '!=', $authUser->id)
                    ->max('last_read_message_id') ?? 0;
                $lastStatus = ($last->id <= $chkOther) ? 'read' : null;
            }
            if ($kelas->activeGroups->isEmpty()) {
                // Show program classrooms even without active sub-groups
                if ($programClassroomIds->contains($kelas->id)) {
                    return [[
                        'type'               => 'classroom',
                        'id'                 => $kelas->id,
                        'kelas_id'           => $kelas->id,
                        'room_key'           => 'kelas:' . $kelas->id,
                        'href'               => route('kelas.chat.room', $kelas->slug),
                        'name'               => $kelas->name,
                        'group_name'         => null,
                        'avatar_color'       => '#0F4C9A',
                        'avatar_initial'     => strtoupper(substr($kelas->name, 0, 1)),
                        'is_classroom'       => true,
                        'last_message'       => $last?->message,
                        'last_message_type'  => $last?->message_type ?? 'text',
                        'last_time'          => $last?->created_at,
                        'last_is_mine'       => $last ? ((int)$last->user_id === (int)$authUser->id && $last->user_type === 'bk') : false,
                        'last_status'        => $lastStatus,
                        'unread'             => $unread,
                        'has_room'           => true,
                    ]];
                }
                return [];
            }
            return $kelas->activeGroups->map(function ($group) use ($kelas, $last, $lastStatus, $unread, $authUser) {
                return [
                    'type'            => 'classroom',
                    'id'              => 'kelas-' . $kelas->id . '-grp-' . $group->id,
                    'kelas_id'        => $kelas->id,
                    'room_key'        => 'kelas:' . $kelas->id . ':grp:' . $group->id,
                    'href'            => route('kelas.chat.room', $kelas->slug),
                    'name'            => $kelas->name,
                    'group_name'      => $group->name,
                    'avatar_color'    => '#0F4C9A',
                    'avatar_initial'  => strtoupper(substr($kelas->name, 0, 1)),
                    'is_classroom'    => true,
                    'last_message'       => $last?->message,
                    'last_message_type'  => $last?->message_type ?? 'text',
                    'last_time'          => $last?->created_at,
                    'last_is_mine'       => $last ? ((int)$last->user_id === (int)$authUser->id && $last->user_type === 'bk') : false,
                    'last_status'        => $lastStatus,
                    'unread'             => $unread,
                    'has_room'           => true,
                ];
            });
        });

        $hiddenKeys    = UserHiddenRoom::hiddenKeysForUser($authUser->id);
        $conversations = $conversations->concat($kelasConvs)
            ->filter(fn($c) => !in_array($c['room_key'], $hiddenKeys))
            ->sortByDesc(fn($c) => $c['last_time']?->timestamp ?? 0)
            ->values();

        $groupsCount = $conversations->filter(fn($c) => !empty($c['is_classroom']))->count();
        $activeFilter = ($filter === 'groups') ? 'groups' : 'all';
        if ($activeFilter === 'groups') {
            $conversations = $conversations->filter(fn($c) => !empty($c['is_classroom']))->values();
        }

        return view('users.sections.chat-list', [
            'mode'          => 'guru',
            'pfx'           => $pfx,
            'conversations' => $conversations,
            'activeFilter'  => $activeFilter,
            'groupsCount'   => $groupsCount,
        ]);
    }

    /**
     * Siswa: chat list (direct 1-on-1) with last message context.
     * Route: GET /siswa/chat
     */
    public function siswaIndex(): View|RedirectResponse
    {
        $authUser = auth()->user();
        abort_unless(($authUser->role ?? 'siswa') !== 'guru', 403);

        $filter = request()->query('filter');

        $gurus = BkAccount::orderBy('name')->get();

        $existingRows = Chat::where('siswa_account_id', $authUser->account_id)
            ->select('room_id', 'guru_account_id')
            ->distinct()
            ->get()
            ->keyBy('guru_account_id');

        $conversations = $gurus->map(function ($guru) use ($authUser, $existingRows) {
            $row    = $existingRows[$guru->account_id] ?? null;
            $roomId = $row?->room_id;
            $last   = $roomId ? Chat::where('room_id', $roomId)->latest()->first() : null;
            $unread = $roomId
                ? Chat::where('room_id', $roomId)
                    ->where('sender_account_id', '!=', $authUser->account_id)
                    ->where('status', 'unread')
                    ->count()
                : 0;

            return [
                'type'           => 'direct',
                'id'             => $roomId ?? $guru->id,
                'room_key'       => $roomId ?? '',
                'href'           => $roomId ? route('chat.room', $roomId) : route('chat.start', $guru->id),
                'name'           => $guru->name,
                'avatar_initial' => strtoupper(substr($guru->name, 0, 1)),
                'avatar_color'   => '#0F4C9A',
                'last_message'      => $last?->message,
                'last_message_type' => $last?->message_type ?? 'text',
                'last_time'         => $last?->created_at,
                'last_is_mine'      => $last ? ($last->sender_account_id === $authUser->account_id) : false,
                'last_status'       => $last?->status,
                'unread'            => $unread,
                'has_room'          => (bool) $roomId,
            ];
        })->filter(fn($c) => $c['has_room'])->sortByDesc(fn($c) => $c['last_time']?->timestamp ?? 0)->values();

        // ── Append E-Konseling system room (read-only) if it exists ─────────
        $sysRoomId = Chat::where('siswa_account_id', $authUser->account_id)
            ->where('guru_account_id', self::SYSTEM_GURU_ACCOUNT_ID)
            ->value('room_id');
        if ($sysRoomId) {
            $last   = Chat::where('room_id', $sysRoomId)->latest()->first();
            $unread = Chat::where('room_id', $sysRoomId)
                ->where('sender_account_id', '!=', $authUser->account_id)
                ->where('status', 'unread')
                ->count();

            $conversations = $conversations->push([
                'type'              => 'direct',
                'id'                => $sysRoomId,
                'room_key'          => $sysRoomId,
                'href'              => route('chat.room', $sysRoomId),
                'name'              => self::SYSTEM_NAME,
                'avatar_initial'    => 'E',
                'avatar_color'      => '#0F4C9A',
                'last_message'      => $last?->message,
                'last_message_type' => $last?->message_type ?? 'text',
                'last_time'         => $last?->created_at,
                'last_is_mine'      => $last ? ($last->sender_account_id === $authUser->account_id) : false,
                'last_status'       => $last?->status,
                'unread'            => $unread,
                'has_room'          => true,
            ])->sortByDesc(fn($c) => $c['last_time']?->timestamp ?? 0)->values();
        }

        // ── Append kelas room (if siswa is in a kelas with an active group containing their absen) ─
        if ($authUser->classroom_id) {
            $kelas            = Classroom::find($authUser->classroom_id);
            $siswaAbsen       = $authUser->absen;
            $activeGroup      = $kelas && $siswaAbsen ? \App\Models\GroupSection::where('classroom_id', $authUser->classroom_id)
                ->where('is_active', true)
                ->where('absen_from', '<=', $siswaAbsen)
                ->where('absen_to',   '>=', $siswaAbsen)
                ->first() : null;
            $inActiveGroup    = (bool) $activeGroup;
            if ($kelas && $inActiveGroup) {
                $last     = GroupMessage::where('classroom_id', $kelas->id)->latest()->first();
                $receipt  = GroupReadReceipt::where('user_id', $authUser->id)
                                ->where('classroom_id', $kelas->id)->first();
                $lastRead = $receipt?->last_read_message_id ?? 0;
                $cutoff   = \App\Models\GroupMemberCutoff::getCutoff($authUser->id, $kelas->id);
                $unread   = GroupMessage::where('classroom_id', $kelas->id)
                                ->where('id', '>', max($lastRead, $cutoff))
                                ->where(function($q) use ($authUser) {
                                    $q->where('user_id', '!=', (int) $authUser->id)
                                      ->orWhere('user_type', '!=', 'siswa');
                                })
                                ->count();
                $lastIsMine = $last && (int)$last->user_id === (int)$authUser->id && $last->user_type === 'siswa';
                $lastStatus = null;
                if ($lastIsMine) {
                    $chkOther = GroupReadReceipt::where('classroom_id', $kelas->id)
                        ->where('user_id', '!=', $authUser->id)
                        ->max('last_read_message_id') ?? 0;
                    $lastStatus = ($last->id <= $chkOther) ? 'read' : null;
                }
                $kelasConv = [
                    'type'            => 'classroom',
                    'id'              => $kelas->id,
                    'room_key'        => 'kelas:' . $kelas->id,
                    'href'            => route('kelas.chat.room', $kelas->slug),
                    'name'            => $kelas->name,
                    'group_name'      => $activeGroup?->name,
                    'avatar_color'    => '#0F4C9A',
                    'avatar_initial'  => strtoupper(substr($kelas->name, 0, 1)),
                    'is_classroom'    => true,
                    'last_message'       => $last?->message,
                    'last_message_type'  => $last?->message_type ?? 'text',
                    'last_time'          => $last?->created_at,
                    'last_is_mine'       => $last ? ((int)$last->user_id === (int)$authUser->id && $last->user_type === 'siswa') : false,
                    'last_status'        => $lastStatus,
                    'unread'             => $unread,
                    'has_room'           => true,
                ];
                $conversations = $conversations->push($kelasConv)
                    ->sortByDesc(fn($c) => $c['last_time']?->timestamp ?? 0)
                    ->values();
            }
        }

        $programClassroomIds = [];

        // ── Append ex-member classrooms (left/removed) ──
        // We rely on GroupMemberCutoff.left_cutoff_message_id as join-history signal.
        $exCutoffs = \App\Models\GroupMemberCutoff::where('user_id', $authUser->id)
            ->whereNotNull('left_cutoff_message_id')
            ->get(['classroom_id', 'cutoff_message_id', 'left_cutoff_message_id']);

        foreach ($exCutoffs as $co) {
            $cid = (string) ($co->classroom_id ?? '');
            if ($cid === '') continue;
            if ($authUser->classroom_id && (string) $authUser->classroom_id === $cid) continue;
            if (in_array($cid, $programClassroomIds, true)) continue;

            $kelas = Classroom::find($cid);
            if (!$kelas) continue;

            $aCutoff = (int) ($co->cutoff_message_id ?? 0);
            $aLeave  = (int) ($co->left_cutoff_message_id ?? 0);
            if ($aLeave <= 0) continue;

            $last = GroupMessage::where('classroom_id', $kelas->id)
                ->when($aCutoff > 0, fn($q) => $q->where('id', '>', $aCutoff))
                ->where('id', '<=', $aLeave)
                ->latest('id')
                ->first();

            $receipt  = GroupReadReceipt::where('user_id', $authUser->id)
                ->where('classroom_id', $kelas->id)->first();
            $lastRead = $receipt?->last_read_message_id ?? 0;
            $unread   = GroupMessage::where('classroom_id', $kelas->id)
                ->where('id', '>', max($lastRead, $aCutoff))
                ->where('id', '<=', $aLeave)
                ->where(function($q) use ($authUser) {
                    $q->where('user_id', '!=', (int) $authUser->id)
                      ->orWhere('user_type', '!=', 'siswa');
                })
                ->count();

            $lastIsMine = $last && (int) $last->user_id === (int) $authUser->id && $last->user_type === 'siswa';
            $lastStatus = null;
            if ($lastIsMine) {
                $chkOther = GroupReadReceipt::where('classroom_id', $kelas->id)
                    ->where('user_id', '!=', $authUser->id)
                    ->max('last_read_message_id') ?? 0;
                $lastStatus = ($last->id <= $chkOther) ? 'read' : null;
            }

            $conversations = $conversations->push([
                'type'               => 'classroom',
                'id'                 => $kelas->id,
                'kelas_id'           => $kelas->id,
                'room_key'           => 'kelas:' . $kelas->id,
                'href'               => route('kelas.chat.room', $kelas->slug),
                'name'               => $kelas->name,
                'group_name'         => null,
                'avatar_color'       => '#0F4C9A',
                'avatar_initial'     => strtoupper(substr($kelas->name, 0, 1)),
                'is_classroom'       => true,
                'last_message'       => $last?->message,
                'last_message_type'  => $last?->message_type ?? 'text',
                'last_time'          => $last?->created_at,
                'last_is_mine'       => $last ? ((int) $last->user_id === (int) $authUser->id && $last->user_type === 'siswa') : false,
                'last_status'        => $lastStatus,
                'unread'             => $unread,
                'is_ex_member'       => true,
                'has_room'           => true,
            ]);
        }
        $conversations = $conversations->sortByDesc(fn($c) => $c['last_time']?->timestamp ?? 0)->values();

        // ── Filter hidden rooms ─────────────────────────────────────────────
        $hiddenKeys    = UserHiddenRoom::hiddenKeysForUser($authUser->id);
        $conversations = $conversations->filter(fn($c) => !in_array($c['room_key'] ?? '', $hiddenKeys))->values();

        $groupsCount = $conversations->filter(fn($c) => !empty($c['is_classroom']))->count();
        $activeFilter = ($filter === 'groups') ? 'groups' : 'all';
        if ($activeFilter === 'groups') {
            $conversations = $conversations->filter(fn($c) => !empty($c['is_classroom']))->values();
        }

        return view('users.sections.chat-list', [
            'mode'          => 'siswa',
            'pfx'           => 'siswa',
            'conversations' => $conversations,
            'activeFilter'  => $activeFilter,
            'groupsCount'   => $groupsCount,
        ]);
    }

    /**
     * Compute / retrieve room_id for a siswa→guru pair and redirect.
     * Route: GET /chat/start/{guruId}  (numeric users.id)
     */
    public function startOrOpen(int $guruId): RedirectResponse
    {
        $authUser = auth()->user();
        $guru     = BkAccount::findOrFail($guruId);
        $roomId   = $this->buildRoomId($authUser, $guru);

        // Pass guru account_id in query so room() can display an empty room
        // Also forward ?embed=1 if coming from an iframe (desktop 2-panel)
        $params = ['roomId' => $roomId, 'guru' => $guru->account_id];
        if (request()->query('embed') === '1') {
            $params['embed'] = '1';
        }
        return redirect()->route('chat.room', $params);
    }

    /**
     * Show the chat room page.
     */
    public function room(Request $request, string $roomId): View
    {
        $authUser = auth()->user();
        $role     = $authUser->role ?? 'user';
        $pfx      = $role === 'guru' ? 'bk' : 'siswa';

        // Resolve participants from existing messages
        $row = Chat::where('room_id', $roomId)
                   ->select('siswa_account_id', 'guru_account_id')
                   ->first();

        if ($row) {
            $siswa = SiswaAccount::where('account_id', $row->siswa_account_id)->first();
            $guru  = $row->guru_account_id === self::SYSTEM_GURU_ACCOUNT_ID
                ? (object) ['account_id' => self::SYSTEM_GURU_ACCOUNT_ID, 'name' => self::SYSTEM_NAME]
                : BkAccount::where('account_id',  $row->guru_account_id)->first();
        } else {
            // Brand-new room (no messages yet) — use ?guru= query param
            $guruAccountId = $request->query('guru', '');

            if ($role === 'guru') {
                // Guru opened an empty room — try to find siswa via reverse lookup
                // (shouldn't normally happen this way, but handle gracefully)
                $guru  = $authUser;
                $siswa = null;
            } else {
                // Siswa: ?guru=GURUX_ACCOUNTID
                if ($guruAccountId === self::SYSTEM_GURU_ACCOUNT_ID) {
                    abort(403, 'Ruang chat tidak valid.');
                }
                $guru  = BkAccount::where('account_id', $guruAccountId)->firstOrFail();
                $siswa = $authUser;
                abort_unless($roomId === $this->buildRoomId($siswa, $guru), 403);
            }
        }

        // Authorization: only participants can view
        abort_unless(
            ($siswa && $authUser->account_id === $siswa->account_id) ||
            ($guru  && $authUser->account_id === $guru->account_id),
            403,
            'Kamu tidak memiliki akses ke ruang chat ini.'
        );

        $other         = ($authUser->account_id === ($siswa?->account_id)) ? $guru : $siswa;
        $guruAccountId = $guru?->account_id ?? '';

        // Last 50 messages
        $messages = Chat::where('room_id', $roomId)
            ->orderBy('created_at')
            ->take(50)
            ->get();

        return view('shared.sections.roomchat', compact(
            'roomId', 'guruAccountId', 'other', 'messages', 'pfx', 'authUser'
        ));
    }

    /**
     * JSON endpoint: messages after a given ID (polling every 2 s).
     */
    public function messages(Request $request, string $roomId): JsonResponse
    {
        $authUser = auth()->user();

        // Authorization: caller must be a participant
        $row = Chat::where('room_id', $roomId)
                   ->select('siswa_account_id', 'guru_account_id')
                   ->first();

        if ($row) {
            abort_unless(
                $authUser->account_id === $row->siswa_account_id ||
                $authUser->account_id === $row->guru_account_id,
                403
            );
        }

        $afterId = (int) $request->query('after', 0);

        // Mark the other person's messages as read (drives blue tick)
        Chat::markRoomRead($roomId, $authUser->account_id);

        $messages = Chat::where('room_id', $roomId)
            ->where('id', '>', $afterId)
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'sender_id'    => $m->sender_account_id,
                'sender_name'  => $this->nameByAccountId($m->sender_account_id),
                'message'      => $m->message,
                'message_type' => $m->message_type ?? 'text',
                'url'          => $m->attachment ? asset('storage/' . $m->attachment) : null,
                'caption'      => in_array($m->message_type, ['image','video']) ? $m->message : null,
                'filename'     => $m->message, // for document: message stores filename
                'time'         => $m->created_at->format('H:i'),
                'is_mine'      => $m->sender_account_id === $authUser->account_id,
                'read'         => $m->status === 'read',
            ]);

        // Tell the sender whether ALL their messages in this room have been read.
        // The recipient marks messages as read on every poll (line above), so this
        // flips within 2 s of the recipient opening the room — no refresh needed.
        $allMineRead = !Chat::where('room_id', $roomId)
            ->where('sender_account_id', $authUser->account_id)
            ->where('status', 'unread')
            ->exists();

        return response()->json(['messages' => $messages, 'all_mine_read' => $allMineRead]);
    }

    /**
     * JSON endpoint polled by the chat-list page every ~3 s.
     * Returns lightweight conversation summaries for real-time list updates.
     */
    public function conversationsJson(Request $request): JsonResponse
    {
        $authUser = auth()->user();
        $role     = $authUser->role ?? 'user';
        $filter   = $request->query('filter');

        if ($role === 'guru') {
            $roomRows = Chat::where('guru_account_id', $authUser->account_id)
                ->select('room_id', 'siswa_account_id')
                ->distinct()
                ->get();

            $conversations = $roomRows->map(function ($row) use ($authUser) {
                $last   = Chat::where('room_id', $row->room_id)->latest()->first();
                $unread = Chat::where('room_id', $row->room_id)
                    ->where('sender_account_id', '!=', $authUser->account_id)
                    ->where('status', 'unread')
                    ->count();
                return [
                    'id'                => $row->room_id,
                    'room_key'          => $row->room_id,
                    'last_message'      => $last?->message,
                    'last_message_type' => $last?->message_type ?? 'text',
                    'last_time_ts'      => $last?->created_at?->timestamp,
                    'last_is_mine'      => $last ? ($last->sender_account_id === $authUser->account_id) : false,
                    'last_status'       => $last?->status,
                    'unread'            => $unread,
                ];
            });

        // ── convJson: kelas for BK — one entry per active group ────────────
        $programClassroomIds = \App\Models\Program::whereNotNull('classroom_id')
            ->whereHas('classroom', fn($q) => $q->where('bk_account_id', $authUser->account_id))
            ->pluck('classroom_id');

        $kelasConvs = Classroom::where('bk_account_id', $authUser->account_id)
            ->with('activeGroups')
            ->orderBy('name')->get()->flatMap(function ($kelas) use ($authUser, $programClassroomIds) {
                $last     = GroupMessage::where('classroom_id', $kelas->id)->latest()->first();
                $receipt  = GroupReadReceipt::where('user_id', $authUser->id)
                                ->where('classroom_id', $kelas->id)->first();
                $lastRead = $receipt?->last_read_message_id ?? 0;
                $unread   = GroupMessage::where('classroom_id', $kelas->id)
                                ->where('id', '>', $lastRead)
                                ->where(function($q) use ($authUser) {
                                    $q->where('user_id', '!=', (int) $authUser->id)
                                      ->orWhere('user_type', '!=', 'bk');
                                })
                                ->count();
                $lastIsMine = $last && (int)$last->user_id === (int)$authUser->id && $last->user_type === 'bk';
                $lastStatus = null;
                if ($lastIsMine) {
                    $chkOther = GroupReadReceipt::where('classroom_id', $kelas->id)
                        ->where('user_id', '!=', $authUser->id)
                        ->max('last_read_message_id') ?? 0;
                    $lastStatus = ($last->id <= $chkOther) ? 'read' : null;
                }
                if ($kelas->activeGroups->isEmpty()) {
                    if ($programClassroomIds->contains($kelas->id)) {
                        return [[
                            'id'                => $kelas->id,
                            'kelas_id'          => $kelas->id,
                            'room_key'          => 'kelas:' . $kelas->id,
                            'name'              => $kelas->name,
                            'group_name'        => null,
                            'avatar_initial'    => strtoupper(substr($kelas->name, 0, 1)),
                            'href'              => route('kelas.chat.room', $kelas->slug),
                            'is_classroom'      => true,
                            'last_message'      => $last?->message,
                            'last_message_type' => $last?->message_type ?? 'text',
                            'last_time_ts'      => $last?->created_at?->timestamp,
                            'last_is_mine'      => $last ? ((int)$last->user_id === (int)$authUser->id && $last->user_type === 'bk') : false,
                            'last_status'       => $lastStatus,
                            'unread'            => $unread,
                            'members_url'       => route('kelas.chat.members', $kelas->id),
                        ]];
                    }
                    return [];
                }
                return $kelas->activeGroups->map(function ($group) use ($kelas, $last, $lastStatus, $unread, $authUser) {
                    return [
                        'id'                => 'kelas-' . $kelas->id . '-grp-' . $group->id,
                        'kelas_id'          => $kelas->id,
                        'room_key'          => 'kelas:' . $kelas->id . ':grp:' . $group->id,
                        'name'              => $kelas->name,
                        'group_name'        => $group->name,
                        'avatar_initial'    => strtoupper(substr($kelas->name, 0, 1)),
                        'href'              => route('kelas.chat.room', $kelas->slug),
                        'is_classroom'      => true,
                        'last_message'      => $last?->message,
                        'last_message_type' => $last?->message_type ?? 'text',
                        'last_time_ts'      => $last?->created_at?->timestamp,
                        'last_is_mine'      => $last ? ((int)$last->user_id === (int)$authUser->id && $last->user_type === 'bk') : false,
                        'last_status'       => $lastStatus,
                        'unread'            => $unread,
                        'members_url'       => route('kelas.chat.members', $kelas->id),
                    ];
                });
            });
            $hiddenKeysG = UserHiddenRoom::hiddenKeysForUser($authUser->id);
            $conversations = $conversations->concat($kelasConvs)
                ->filter(fn($c) => !in_array($c['room_key'] ?? '', $hiddenKeysG))
                ->sortByDesc(fn($c) => $c['last_time_ts'] ?? 0)->values();
        } else {
            $gurus = BkAccount::get();
            $existingRows = Chat::where('siswa_account_id', $authUser->account_id)
                ->select('room_id', 'guru_account_id')
                ->distinct()
                ->get()
                ->keyBy('guru_account_id');

            $conversations = $gurus->map(function ($guru) use ($authUser, $existingRows) {
                $row    = $existingRows[$guru->account_id] ?? null;
                $roomId = $row?->room_id;
                if (!$roomId) return null; // skip gurus with no chat room yet
                $last   = Chat::where('room_id', $roomId)->latest()->first();
                $unread = Chat::where('room_id', $roomId)
                    ->where('sender_account_id', '!=', $authUser->account_id)
                    ->where('status', 'unread')
                    ->count();
                return [
                    'id'                => $roomId,
                    'room_key'          => $roomId ?? '',
                    'name'              => $guru->name,
                    'avatar_initial'    => strtoupper(substr($guru->name, 0, 1)),
                    'href'              => route('chat.room', $roomId),
                    'last_message'      => $last?->message,
                    'last_message_type' => $last?->message_type ?? 'text',
                    'last_time_ts'      => $last?->created_at?->timestamp,
                    'last_is_mine'      => $last ? ($last->sender_account_id === $authUser->account_id) : false,
                    'last_status'       => $last?->status,
                    'unread'            => $unread,
                ];
            })->filter()->values();

            // Append E-Konseling system room if it exists
            $sysRoomId = ($existingRows[self::SYSTEM_GURU_ACCOUNT_ID] ?? null)?->room_id
                ?? Chat::where('siswa_account_id', $authUser->account_id)
                    ->where('guru_account_id', self::SYSTEM_GURU_ACCOUNT_ID)
                    ->value('room_id');
            if ($sysRoomId) {
                $last   = Chat::where('room_id', $sysRoomId)->latest()->first();
                $unread = Chat::where('room_id', $sysRoomId)
                    ->where('sender_account_id', '!=', $authUser->account_id)
                    ->where('status', 'unread')
                    ->count();

                $conversations = $conversations->push([
                    'id'                => $sysRoomId,
                    'room_key'          => $sysRoomId,
                    'name'              => self::SYSTEM_NAME,
                    'avatar_initial'    => 'E',
                    'href'              => route('chat.room', $sysRoomId),
                    'last_message'      => $last?->message,
                    'last_message_type' => $last?->message_type ?? 'text',
                    'last_time_ts'      => $last?->created_at?->timestamp,
                    'last_is_mine'      => $last ? ($last->sender_account_id === $authUser->account_id) : false,
                    'last_status'       => $last?->status,
                    'unread'            => $unread,
                ]);
            }

            // Include kelas (classroom) conversation if siswa is in one AND absen in active group
            if ($authUser->classroom_id) {
                $siswaAbsen  = $authUser->absen;
                $nisInActive = $siswaAbsen && \App\Models\GroupSection::where('classroom_id', $authUser->classroom_id)
                    ->where('is_active', true)
                    ->where('absen_from', '<=', $siswaAbsen)
                    ->where('absen_to',   '>=', $siswaAbsen)
                    ->exists();
                if ($nisInActive) {
                $kelas    = Classroom::find($authUser->classroom_id);
                $kelasLast = GroupMessage::where('classroom_id', $authUser->classroom_id)->latest()->first();
                $receipt  = GroupReadReceipt::where('user_id', $authUser->id)
                                ->where('classroom_id', $authUser->classroom_id)->first();
                $lastRead = $receipt?->last_read_message_id ?? 0;
                $kCutoff  = \App\Models\GroupMemberCutoff::getCutoff($authUser->id, $authUser->classroom_id);
                $kelasUnread = GroupMessage::where('classroom_id', $authUser->classroom_id)
                                ->where('id', '>', max($lastRead, $kCutoff))
                                ->where(function($q) use ($authUser) {
                                    $q->where('user_id', '!=', (int) $authUser->id)
                                      ->orWhere('user_type', '!=', 'siswa');
                                })
                                ->count();
                $kelasIsMine = $kelasLast && (int)$kelasLast->user_id === (int)$authUser->id && $kelasLast->user_type === 'siswa';
                $kelasStatus = null;
                if ($kelasIsMine) {
                    $chkKelas = GroupReadReceipt::where('classroom_id', $authUser->classroom_id)
                        ->where('user_id', '!=', $authUser->id)
                        ->max('last_read_message_id') ?? 0;
                    $kelasStatus = ($kelasLast->id <= $chkKelas) ? 'read' : null;
                }
                $conversations = $conversations->push([
                    'id'                => $authUser->classroom_id,
                    'room_key'          => 'kelas:' . $authUser->classroom_id,
                    'name'              => $kelas?->name ?? 'Kelas',
                    'avatar_initial'    => strtoupper(substr($kelas?->name ?? 'K', 0, 1)),
                    'href'              => route('kelas.chat.room', $kelas->slug),
                    'is_classroom'      => true,
                    'last_message'      => $kelasLast?->message,
                    'last_message_type' => $kelasLast?->message_type ?? 'text',
                    'last_time_ts'      => $kelasLast?->created_at?->timestamp,
                    'last_is_mine'      => $kelasLast ? ((int)$kelasLast->user_id === (int)$authUser->id && $kelasLast->user_type === 'siswa') : false,
                    'last_status'       => $kelasStatus,
                    'unread'            => $kelasUnread,
                    'members_url'       => route('kelas.chat.members', $authUser->classroom_id),
                ]);
                }
            }

            // Program classrooms are not shown in chat-list.
        }
        $conversations = $conversations->sortByDesc(fn($c) => $c['last_time_ts'] ?? 0);

        $hiddenKeysS = UserHiddenRoom::hiddenKeysForUser($authUser->id);
        $conversations = $conversations->filter(fn($c) => !in_array($c['room_key'] ?? '', $hiddenKeysS));

        if ($filter === 'groups') {
            $conversations = $conversations->filter(fn($c) => !empty($c['is_classroom']));
        }

        return response()->json($conversations->values());
    }

    /**
     * Send a message to the room.
     */
    public function send(Request $request, string $roomId): JsonResponse
    {
        $authUser = auth()->user();

        // Resolve room participants
        $row = Chat::where('room_id', $roomId)
                   ->select('siswa_account_id', 'guru_account_id')
                   ->first();

        if ($row) {
            $siswaAccountId = $row->siswa_account_id;
            $guruAccountId  = $row->guru_account_id;
        } else {
            // First message in a brand-new room (siswa initiating)
            abort_if($authUser->role === 'guru', 422, 'Ruang chat tidak valid.');

            $guruAccountId = $request->input('guru_account_id', '');
            abort_if(!$guruAccountId, 422, 'guru_account_id diperlukan untuk ruang baru.');

            $guru = BkAccount::where('account_id', $guruAccountId)->firstOrFail();
            abort_unless($roomId === $this->buildRoomId($authUser, $guru), 403);

            $siswaAccountId = $authUser->account_id;
        }

        // System room is read-only
        abort_if($guruAccountId === self::SYSTEM_GURU_ACCOUNT_ID, 403, 'Chat E-Konseling hanya untuk pesan pengingat dari sistem.');

        abort_unless(
            $authUser->account_id === $siswaAccountId ||
            $authUser->account_id === $guruAccountId,
            403
        );

        $validated = $request->validate(['message' => 'required|string|max:2000']);

        $msg = Chat::create([
            'room_id'           => $roomId,
            'siswa_account_id'  => $siswaAccountId,
            'guru_account_id'   => $guruAccountId,
            'sender_account_id' => $authUser->account_id,
            'sender_role'       => $authUser->role,
            'message'           => $validated['message'],
            'message_type'      => 'text',
            'status'            => 'unread',
        ]);

        return response()->json([
            'id'          => $msg->id,
            'sender_id'   => $authUser->account_id,
            'sender_name' => $authUser->name,
            'message'     => $msg->message,
            'time'        => $msg->created_at->format('H:i'),
            'is_mine'     => true,
            'read'        => false,
        ]);
    }

    /**
     * Upload and send a media/document message.
     * Accepts multipart/form-data with file=<binary> and optional caption=<string>.
     */
    public function sendMedia(Request $request, string $roomId): JsonResponse
    {
        $authUser = auth()->user();

        $request->validate([
            'file'    => 'required|file|max:20480', // 20 MB max
            'caption' => 'nullable|string|max:500',
        ]);

        // Resolve participants
        $row = Chat::where('room_id', $roomId)
                   ->select('siswa_account_id', 'guru_account_id')
                   ->first();

        if ($row) {
            $siswaAccountId = $row->siswa_account_id;
            $guruAccountId  = $row->guru_account_id;
        } else {
            abort_if($authUser->role === 'guru', 422, 'Ruang chat tidak valid.');
            $guruAccountId = $request->input('guru_account_id', '');
            abort_if(!$guruAccountId, 422, 'guru_account_id diperlukan.');
            $guru = BkAccount::where('account_id', $guruAccountId)->firstOrFail();
            abort_unless($roomId === $this->buildRoomId($authUser, $guru), 403);
            $siswaAccountId = $authUser->account_id;
        }

        // System room is read-only
        abort_if($guruAccountId === self::SYSTEM_GURU_ACCOUNT_ID, 403, 'Chat E-Konseling hanya untuk pesan pengingat dari sistem.');

        abort_unless(
            $authUser->account_id === $siswaAccountId ||
            $authUser->account_id === $guruAccountId,
            403
        );

        $file     = $request->file('file');
        $mime     = $file->getMimeType() ?? '';
        $type     = str_starts_with($mime, 'image/') ? 'image'
                  : (str_starts_with($mime, 'video/') ? 'video' : 'document');

        // Build custom filename: IMG-E-KONSELING-YYYYMMDD-XXXX.ext (uppercase)
        $ext      = strtoupper($file->getClientOriginalExtension() ?: 'BIN');
        $rand     = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
        $dateStr  = now()->format('d-m-y');
        if ($type === 'image') {
            $newName = "IMG-E-KONSELING-{$dateStr}-{$rand}.{$ext}";
        } elseif ($type === 'video') {
            $newName = "VID-E-KONSELING-{$dateStr}-{$rand}.{$ext}";
        } else {
            $newName = strtoupper($file->getClientOriginalName());
        }
        $path     = $file->storeAs("chat/{$roomId}", $newName, 'public');
        $url      = asset('storage/' . $path);

        $caption  = $request->input('caption') ?? '';
        // For images/video: store only the caption as message (empty string if none)
        // For documents: store caption if provided, else original filename
        // Note: ConvertEmptyStringsToNull middleware turns '' into null on input;
        //       the ?? '' above ensures $caption is always a string, never null.
        $message  = ($type === 'document') ? ($caption ?: $file->getClientOriginalName()) : $caption;

        $msg = Chat::create([
            'room_id'           => $roomId,
            'siswa_account_id'  => $siswaAccountId,
            'guru_account_id'   => $guruAccountId,
            'sender_account_id' => $authUser->account_id,
            'sender_role'       => $authUser->role,
            'message'           => $message,
            'message_type'      => $type,
            'attachment'        => $path,
            'status'            => 'unread',
        ]);

        return response()->json([
            'id'           => $msg->id,
            'sender_id'    => $authUser->account_id,
            'message_type' => $type,
            'url'          => $url,
            'caption'      => $caption,
            'filename'     => $newName,
            'time'         => $msg->created_at->format('H:i'),
            'is_mine'      => true,
            'read'         => false,
        ]);
    }

    /**
     * Delete (hide or hard-delete) selected conversations.
     * POST /api/chat/delete-conversations
     *
     * Body: { rooms: [{room_key, is_classroom}], for_everyone: bool }
     */
    public function deleteConversations(Request $request): JsonResponse
    {
        $authUser = auth()->user();
        $isGuru   = ($authUser->role ?? '') === 'guru';
        $forEvery = (bool) $request->input('for_everyone', false);

        foreach ((array) $request->input('rooms', []) as $room) {
            $roomKey     = $room['room_key']     ?? '';
            $isClassroom = (bool) ($room['is_classroom'] ?? false);
            if (!$roomKey) continue;

            if ($forEvery && $isGuru && !$isClassroom) {
                // Hard-delete all messages in this direct chat room
                Chat::where('room_id', $roomKey)
                    ->where('guru_account_id', $authUser->account_id)
                    ->delete();
                // Remove any stale hidden-room entries for this room
                UserHiddenRoom::where('room_key', $roomKey)->delete();
            } else {
                // Soft-hide for current user only
                UserHiddenRoom::hide($authUser->id, $roomKey);
            }
        }

        return response()->json(['ok' => true]);
    }
}
