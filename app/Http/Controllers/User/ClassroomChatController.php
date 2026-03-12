<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BkAccount;
use App\Models\Classroom;
use App\Models\ClassroomJoinRequest;
use App\Models\ClassroomMessage;
use App\Models\ClassroomReadReceipt;
use App\Models\SiswaAccount;
use App\Models\Users\UserNotification;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClassroomChatController extends Controller
{
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
            // For agenda classrooms: approved join request = full member
            $isAgendaKelas = \App\Models\Agenda::where('classroom_id', $kelas->id)->exists();
            if ($isAgendaKelas) {
                $isFull = $user->classroom_id == $kelas->id
                    || ClassroomJoinRequest::where('user_id', $user->id)
                        ->where('classroom_id', $kelas->id)
                        ->where('status', 'approved')
                        ->exists();
                abort_unless($isFull, 403, 'Kamu belum bergabung ke kelas ini.');
            } else {
                // Regular kelas: must be primary classroom member
                abort_unless($user->classroom_id == $kelas->id, 403, 'Kamu belum bergabung ke kelas ini.');
            }
        } else {
            // Read-only: allow current members AND ex-members (approved before)
            $ok = $user->classroom_id == $kelas->id || ClassroomJoinRequest::where('user_id', $user->id)
                    ->where('classroom_id', $kelas->id)
                    ->where('status', 'approved')
                    ->exists();
            abort_unless($ok, 403, 'Kamu belum pernah bergabung ke kelas ini.');
        }
    }

    /**
     * Chat room view for a classroom.
     * GET /kelas/{slug}/chat  (slug = Str::slug(name), e.g. "xii-rpl")
     */
    public function room(string $slug): View|RedirectResponse
    {
        $kelas = Classroom::all()->first(fn($c) => \Illuminate\Support\Str::slug($c->name) === $slug);
        abort_if(!$kelas, 404);
        $authUser = auth()->user();

        $isAgendaKelas    = \App\Models\Agenda::where('classroom_id', $kelas->id)->exists();
        $isApprovedMember = ClassroomJoinRequest::where('user_id', $authUser->id)
            ->where('classroom_id', $kelas->id)
            ->where('status', 'approved')
            ->exists();
        $isMember = $authUser->role === 'guru'
            || $authUser->classroom_id == $kelas->id
            || ($isAgendaKelas && $isApprovedMember);

        if (!$isMember) {
            if (!$isAgendaKelas && $isApprovedMember) {
                // Ex-member of regular kelas → read-only
                $this->assertAccess($kelas, false);
            } else {
                // Never joined → show join modal on chat list
                return redirect(route('siswa.chat') . '?join=' . urlencode($kelas->join_token));
            }
        } else {
            $this->assertAccess($kelas);
        }

        $memberCount  = $kelas->students()->count();
        $messages     = ClassroomMessage::where('classroom_id', $kelas->id)
            ->with('sender:id,name,role')
            ->orderBy('created_at')
            ->get();

        // Mark all current messages as read
        $latestId = $messages->max('id') ?? 0;
        if ($latestId) {
            ClassroomReadReceipt::updateOrCreate(
                ['user_id' => $authUser->id, 'classroom_id' => $kelas->id],
                ['last_read_message_id' => $latestId]
            );
        }

        return view('shared.sections.roomchat', [
            'isKelas'      => true,
            'kelas'        => $kelas,
            'messages'     => $messages,
            'authUser'     => $authUser,
            'memberCount'  => $memberCount,
            'isMember'     => $isMember,
            'pfx'          => $authUser->role === 'guru' ? 'bk' : 'siswa',
            'isAgendaKelas' => $isAgendaKelas,
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

        $query = ClassroomMessage::where('classroom_id', $id)
            ->with('sender:id,name,role')
            ->orderBy('created_at');

        if ($since) {
            $query->where('id', '>', (int) $since);
        } else {
            $query->latest()->limit(100)->reorder()->orderBy('created_at');
        }

        $msgs = $query->get()->map(fn($m) => [
            'id'           => $m->id,
            'user_id'      => $m->user_id,
            'is_mine'      => $m->user_id === $authUser->id,
            'sender_name'  => $m->sender?->name ?? '—',
            'sender_role'  => $m->sender?->role ?? '',
            'message'      => $m->message,
            'message_type' => $m->message_type,
            'attachment'   => $m->attachment ? asset('storage/' . $m->attachment) : null,
            'time'         => $m->created_at->format('H:i'),
            'timestamp'    => $m->created_at->toISOString(),
        ]);

        return response()->json(['messages' => $msgs]);
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

        $msg = ClassroomMessage::create([
            'classroom_id' => $id,
            'user_id'      => $authUser->id,
            'message'      => $data['message'] ?? null,
            'message_type' => $type,
            'attachment'   => $attachment,
        ]);

        $msg->load('sender:id,name,role');

        return response()->json([
            'id'           => $msg->id,
            'user_id'      => $msg->user_id,
            'is_mine'      => true,
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

        $siswa = SiswaAccount::where('classroom_id', $id)
            ->orderBy('name')
            ->get(['id', 'name', 'login_id', 'account_id']);

        $guru = BkAccount::where('account_id', $kelas->bk_account_id)
            ->get(['id', 'name', 'login_id', 'account_id']);

        $members = $guru->concat($siswa);

        return response()->json([
            'classroom_name' => $kelas->name,
            'member_count'   => $siswa->count(),
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
        $latestId = ClassroomMessage::where('classroom_id', $id)->max('id') ?? 0;

        ClassroomReadReceipt::updateOrCreate(
            ['user_id' => $authUser->id, 'classroom_id' => $id],
            ['last_read_message_id' => $latestId]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Show the join-via-link page.
     * GET /kelas/join/{token}
     */
    public function joinPage(string $token): View|RedirectResponse
    {
        $kelas    = Classroom::where('join_token', $token)->firstOrFail();
        $authUser = auth()->user();

        // Guru cannot join as student
        if ($authUser->role === 'guru') {
            return redirect()->route('bk.kelas')
                ->with('error', 'Guru tidak perlu bergabung ke kelas.');
        }

        // Already a member of this kelas — go straight to chat
        if ($authUser->classroom_id == $kelas->id) {
            return redirect()->route('kelas.chat.room', $kelas->slug);
        }

        // Already has a pending request — show pending notice
        $existingRequest = ClassroomJoinRequest::where('classroom_id', $kelas->id)
            ->where('user_id', $authUser->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return redirect(route('siswa.chat') . '?join=' . urlencode($token) . '&pending=1');
        }

        // Render join modal over the chat list
        return redirect(route('siswa.chat') . '?join=' . urlencode($token));
    }

    /**
     * Confirm joining a classroom — creates a PENDING request (awaits BK approval).
     * POST /kelas/join/{token}
     */
    public function joinConfirm(string $token): RedirectResponse
    {
        $kelas    = Classroom::where('join_token', $token)->firstOrFail();
        $authUser = auth()->user();

        abort_if($authUser->role === 'guru', 403);

        // Already a member — nothing to do
        if ($authUser->classroom_id == $kelas->id) {
            return redirect()->route('kelas.chat.room', $kelas->slug);
        }

        // Upsert: create or reset a previous rejected request to pending
        ClassroomJoinRequest::updateOrCreate(
            ['classroom_id' => $kelas->id, 'user_id' => $authUser->id],
            ['status' => 'pending', 'note' => null, 'responded_at' => null]
        );

        // Notify the BK who owns this classroom
        $bkUser = BkAccount::where('account_id', $kelas->bk_account_id)->first();
        if ($bkUser) {
            $notifTitle = 'Permintaan Bergabung Kelas';
            $notifBody  = $authUser->name . ' meminta bergabung ke ' . $kelas->name;

            // Persist in-app notification
            UserNotification::create([
                'user_id'      => $bkUser->id,
                'type'         => 'kelas_join_request',
                'title'        => $notifTitle,
                'body'         => $notifBody,
                'related_id'   => null,
                'related_type' => 'classroom:' . $kelas->id,
            ]);

            // Push notification
            try {
                app(PushNotificationService::class)->sendToUser(
                    $bkUser->id,
                    $notifTitle,
                    $notifBody,
                    ['url' => route('bk.agenda.verifikasi')]
                );
            } catch (\Throwable) {
                // Push not critical — ignore
            }
        }

        // Redirect back with pending flag so the UI can show the waiting message
        return redirect(route('siswa.chat') . '?join=' . urlencode($token) . '&pending=1');
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

        $msg = ClassroomMessage::create([
            'classroom_id' => $id,
            'user_id'      => $authUser->id,
            'message'      => $caption,
            'message_type' => $type,
            'attachment'   => $path,
        ]);

        return response()->json([
            'id'           => $msg->id,
            'user_id'      => $msg->user_id,
            'is_mine'      => true,
            'sender_name'  => $authUser->name,
            'sender_role'  => $authUser->role,
            'message'      => $msg->message,
            'message_type' => $msg->message_type,
            'attachment'   => asset('storage/' . $path),
            'filename'     => $file->getClientOriginalName(),
            'time'         => $msg->created_at->format('H:i'),
        ], 201);
    }

    /**
     * Siswa polls this to know if their join request was approved/rejected.
     * GET /api/kelas/join/status?token=XXX
     */
    public function joinStatus(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $token = $request->query('token');

        $kelas = Classroom::where('join_token', $token)->first();
        if (!$kelas) {
            return response()->json(['status' => 'not_found'], 404);
        }

        // Already a member?
        if ($user->classroom_id == $kelas->id) {
            return response()->json([
                'status'  => 'approved',
                'roomUrl' => route('kelas.chat.room', $kelas->slug),
            ]);
        }

        $req = ClassroomJoinRequest::where('classroom_id', $kelas->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (!$req) {
            return response()->json(['status' => 'none']);
        }

        $payload = ['status' => $req->status];
        if ($req->status === 'approved') {
            $payload['roomUrl'] = route('kelas.chat.room', $kelas->slug);
        }
        if ($req->status === 'rejected') {
            $payload['note'] = $req->note ?? null;
        }

        return response()->json($payload);
    }
}
