<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BkAccount;
use App\Models\Classroom;
use App\Models\GroupMemberCutoff;
use App\Models\GroupMessage;
use App\Models\GroupReadReceipt;
use App\Models\GroupSection;
use App\Models\SiswaAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GroupChatController extends Controller
{
    /** Return the message-history cutoff ID for the current user in a classroom (0 = no cutoff). */
    private function getMessageCutoff(int $userId, string $classroomId): int
    {
        if (auth()->user()?->role === 'guru') return 0;
        return GroupMemberCutoff::getCutoff($userId, $classroomId);
    }

    /** Check the authenticated user can access this classroom. */
    private function assertAccess(Classroom $kelas, bool $memberOnly = true): void
    {
        $user = auth()->user();

        // Guru BK can only access classrooms they created
        if ($user->role === 'guru') {
            abort_unless(
                $user->account_id === $kelas->bk_account_id,
                403,
                'Kamu bukan pengelola kelas ini.'
            );
            return;
        }

        if ($memberOnly) {
            // Regular kelas: must be primary classroom member
            abort_unless($user->classroom_id == $kelas->id, 403, 'Kamu belum bergabung ke kelas ini.');
            return;
        }

        // Read-only: allow current members AND ex-members with leave cutoff.
        $ok = $user->classroom_id == $kelas->id;
        if (!$ok && ($user->role ?? null) !== 'guru') {
            $leaveCutoff = GroupMemberCutoff::resolveLeaveCutoff((int) $user->id, (string) $kelas->id);
            $ok = $leaveCutoff > 0;
        }
        abort_unless($ok, 403, 'Kamu belum pernah bergabung ke kelas ini.');
    }

    /**
     * Chat room view for a classroom.
     * GET /kelas/{slug}/chat  (slug = Str::slug(name), e.g. "xii-rpl")
     */
    public function room(string $slug): View
    {
        $kelas = Classroom::all()->first(fn($c) => \Illuminate\Support\Str::slug($c->name) === $slug);
        abort_if(!$kelas, 404);
        $authUser = auth()->user();

        $isMember = $authUser->role === 'guru' || $authUser->classroom_id == $kelas->id;
        if ($isMember) {
            $this->assertAccess($kelas);
        } else {
            // Ex-member/history member → read-only if they have a leave cutoff.
            $this->assertAccess($kelas, false);
        }

        $memberCount  = (function () use ($kelas, $authUser) {
            // For kelas, show group members count for siswa based on active GroupSection.
            if (($authUser->role ?? '') !== 'guru') {
                $absen = (int) ($authUser->absen ?? 0);
                if ($absen > 0) {
                    $grp = GroupSection::where('classroom_id', $kelas->id)
                        ->where('is_active', true)
                        ->where('absen_from', '<=', $absen)
                        ->where('absen_to', '>=', $absen)
                        ->first();
                    if ($grp) {
                        return SiswaAccount::where('classroom_id', $kelas->id)
                            ->whereBetween('absen', [$grp->absen_from, $grp->absen_to])
                            ->count();
                    }
                }
            }
            return $kelas->students()->count();
        })();
        $cutoff       = $this->getMessageCutoff($authUser->id, $kelas->id);
        $leaveCutoff  = (!$isMember && $authUser->role !== 'guru')
            ? GroupMemberCutoff::resolveLeaveCutoff((int) $authUser->id, (string) $kelas->id)
            : 0;
        $messages     = GroupMessage::where('classroom_id', $kelas->id)
            ->when($cutoff > 0, fn($q) => $q->where('id', '>', $cutoff))
            ->when($leaveCutoff > 0, fn($q) => $q->where('id', '<=', $leaveCutoff))
            ->with('sender:id,name')
            ->orderBy('created_at')
            ->get();

        // Mark all current messages as read
        $latestId = $messages->max('id') ?? 0;
        if ($latestId) {
            GroupReadReceipt::updateOrCreate(
                ['user_id' => $authUser->id, 'classroom_id' => $kelas->id],
                ['last_read_message_id' => $latestId]
            );
        }

        $maxOtherRead = GroupReadReceipt::where('classroom_id', $kelas->id)
            ->where('user_id', '!=', $authUser->id)
            ->max('last_read_message_id') ?? 0;

        return view('shared.sections.roomchat', [
            'isKelas'      => true,
            'kelas'        => $kelas,
            'messages'     => $messages,
            'authUser'     => $authUser,
            'memberCount'  => $memberCount,
            'isMember'     => $isMember,
            'pfx'          => $authUser->role === 'guru' ? 'bk' : 'siswa',
            'maxOtherRead' => $maxOtherRead,
        ]);
    }

    /**
     * JSON: latest messages (last 100).
     * GET /api/kelas/{id}/chat/messages
     */
    public function messages(string $id): JsonResponse
    {
        $kelas = Classroom::findOrFail($id);
        $this->assertAccess($kelas, false); // allow ex-members to read

        $authUser = auth()->user();
        $since    = request()->query('since');
        $cutoff   = $this->getMessageCutoff($authUser->id, $id);

        // Determine current membership (used to block new messages for ex-members)
        $isMember = $authUser->role === 'guru' || $authUser->classroom_id == $kelas->id;
        $leaveCutoff = (!$isMember && $authUser->role !== 'guru')
            ? GroupMemberCutoff::resolveLeaveCutoff((int) $authUser->id, (string) $kelas->id)
            : 0;

        $query = GroupMessage::where('classroom_id', $id)
            ->with('sender:id,name')
            ->orderBy('created_at');

        // Ex-members: never return messages newer than leaveCutoff
        if ($leaveCutoff > 0) {
            $query->where('id', '<=', $leaveCutoff);
        }

        if ($since) {
            // If the client already has everything up to leaveCutoff, return empty.
            if ($leaveCutoff > 0 && (int) $since >= $leaveCutoff) {
                $maxOtherRead = GroupReadReceipt::where('classroom_id', $id)
                    ->where('user_id', '!=', $authUser->id)
                    ->max('last_read_message_id') ?? 0;
                return response()->json(['messages' => [], 'max_other_read' => $maxOtherRead]);
            }
            $query->where('id', '>', max((int) $since, $cutoff));
        } else {
            if ($cutoff > 0) {
                $query->where('id', '>', $cutoff);
            } else {
                $query->latest()->limit(100)->reorder()->orderBy('created_at');
            }
        }

        $myType = $authUser->role === 'guru' ? 'bk' : 'siswa';
        $maxOtherRead = GroupReadReceipt::where('classroom_id', $id)
            ->where('user_id', '!=', $authUser->id)
            ->max('last_read_message_id') ?? 0;
        $msgs = $query->get()->map(fn($m) => [
            'id'           => $m->id,
            'user_id'      => $m->user_id,
            'is_mine'      => (int) $m->user_id === (int) $authUser->id && $m->user_type === $myType,
            'read'         => (int) $m->user_id === (int) $authUser->id && $m->user_type === $myType && $m->id <= $maxOtherRead,
            'sender_name'  => $m->sender?->name ?? ($m->user_type === 'bk' ? 'Guru BK' : '—'),
            'sender_role'  => $m->sender?->role ?? ($m->user_type === 'bk' ? 'guru' : 'siswa'),
            'message'      => $m->message,
            'message_type' => $m->message_type,
            'attachment'   => $m->attachment ? asset('storage/' . $m->attachment) : null,
            'time'         => $m->created_at->format('H:i'),
            'timestamp'    => $m->created_at->toISOString(),
        ]);

        return response()->json(['messages' => $msgs, 'max_other_read' => $maxOtherRead]);
    }

    /**
     * Send a message to a classroom.
     * POST /api/kelas/{id}/chat/send
     */

    public function send(Request $request, string $id): JsonResponse
    {
        $kelas = Classroom::findOrFail($id);
        $this->assertAccess($kelas);

        $authUser = auth()->user();

        $data = $request->validate([
            'message'      => 'nullable|string|max:4000',
            'message_type' => 'nullable|string|in:text,image,document',
            'attachment'   => 'nullable|file|max:10240',
        ]);

        $type       = $data['message_type'] ?? 'text';
        $attachment = null;

        if ($request->hasFile('attachment')) {
            $path       = $request->file('attachment')->store('classroom_attachments', 'public');
            $attachment = $path;
        }

        $msg = GroupMessage::create([
            'classroom_id' => $id,
            'user_id'      => $authUser->id,
            'user_type'    => $authUser->role === 'guru' ? 'bk' : 'siswa',
            'message'      => $data['message'] ?? null,
            'message_type' => $type,
            'attachment'   => $attachment,
        ]);

        // sender_name/role taken directly from $authUser below — no need to load relation

        return response()->json([
            'id'           => $msg->id,
            'user_id'      => $msg->user_id,
            'is_mine'      => true,
            'read'         => false,
            'sender_name'  => $authUser->name,
            'sender_role'  => $authUser->role,
            'message'      => $msg->message,
            'message_type' => $msg->message_type,
            'attachment'   => $msg->attachment ? asset('storage/' . $msg->attachment) : null,
            'time'         => $msg->created_at->format('H:i'),
            'timestamp'    => $msg->created_at->toISOString(),
        ], 201);
    }

    /**
     * List members of a classroom (accessible by members and guru).
     * GET /api/kelas/{id}/chat/members
     */
    public function members(string $id): JsonResponse
    {
        $kelas = Classroom::findOrFail($id);
        $this->assertAccess($kelas);

        $authUser = auth()->user();
        $query = SiswaAccount::where('classroom_id', $id);

        // For siswa viewers: restrict to their active group section range.
        if (($authUser->role ?? '') !== 'guru') {
            $absen = (int) ($authUser->absen ?? 0);
            if ($absen > 0) {
                $grp = GroupSection::where('classroom_id', $id)
                    ->where('is_active', true)
                    ->where('absen_from', '<=', $absen)
                    ->where('absen_to', '>=', $absen)
                    ->first();
                if ($grp) {
                    $query->whereBetween('absen', [$grp->absen_from, $grp->absen_to]);
                }
            }
        }

        $siswa = $query->orderBy('name')
            ->get(['id', 'name', 'login_id', 'account_id'])
            ->map(fn($s) => [
                'id'         => $s->id,
                'name'       => $s->name,
                'login_id'   => $s->login_id,
                'account_id' => $s->account_id,
                'role'       => 'siswa',
            ]);

        $guru = BkAccount::where('account_id', $kelas->bk_account_id)
            ->get(['id', 'name', 'login_id', 'account_id'])
            ->map(fn($g) => [
                'id'         => $g->id,
                'name'       => $g->name,
                'login_id'   => $g->login_id,
                'account_id' => $g->account_id,
                'role'       => 'guru',
            ]);

        $members = $guru->concat($siswa);

        return response()->json([
            'classroom_name' => $kelas->name,
            'member_count'   => $siswa->count(),
            'is_program'     => false,
            'members'        => $members,
        ]);
    }

    /**
     * Mark messages as read for authenticated user.
     * POST /api/kelas/{id}/chat/mark-read
     */
    public function markRead(string $id): JsonResponse
    {
        $kelas    = Classroom::findOrFail($id);
        $this->assertAccess($kelas);
        $authUser = auth()->user();
        $latestId = GroupMessage::where('classroom_id', $id)->max('id') ?? 0;

        GroupReadReceipt::updateOrCreate(
            ['user_id' => $authUser->id, 'classroom_id' => $id],
            ['last_read_message_id' => $latestId]
        );

        return response()->json(['ok' => true]);
    }


    /**
     * Upload a media/document file to the classroom chat.
     * POST /api/kelas/{id}/chat/send-media
     */
    public function sendMedia(Request $request, string $id): JsonResponse
    {
        $kelas = Classroom::findOrFail($id);
        $this->assertAccess($kelas);
        $authUser = auth()->user();

        $request->validate(['file' => 'required|file|max:20480']);
        $file = $request->file('file');
        $mime = $file->getMimeType() ?? '';

        if (str_starts_with($mime, 'video/')) {
            $type = 'video';
        } elseif (str_starts_with($mime, 'image/')) {
            $type = 'image';
        } else {
            $type = 'document';
        }

        $path    = $file->store('classroom_attachments', 'public');
        $caption = $request->input('caption') ?: null;

        $msg = GroupMessage::create([
            'classroom_id' => $id,
            'user_id'      => $authUser->id,
            'user_type'    => $authUser->role === 'guru' ? 'bk' : 'siswa',
            'message'      => $caption,
            'message_type' => $type,
            'attachment'   => $path,
        ]);

        return response()->json([
            'id'           => $msg->id,
            'user_id'      => $msg->user_id,
            'is_mine'      => true,
            'read'         => false,
            'sender_name'  => $authUser->name,
            'sender_role'  => $authUser->role,
            'message'      => $msg->message,
            'message_type' => $msg->message_type,
            'attachment'   => asset('storage/' . $path),
            'filename'     => $file->getClientOriginalName(),
            'time'         => $msg->created_at->format('H:i'),
        ], 201);
    }

}
