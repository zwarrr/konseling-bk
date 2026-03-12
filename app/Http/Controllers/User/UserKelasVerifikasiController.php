<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomJoinRequest;
use App\Models\ClassroomMessage;
use App\Models\Users\UserNotification;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserKelasVerifikasiController extends Controller
{
    /** Guard: BK (guru) only. */
    private function assertGuru(): void
    {
        abort_unless(auth()->user()?->role === 'guru', 403);
    }

    /**
     * Halaman verifikasi siswa — tampil daftar permintaan bergabung.
     * GET /bk/kelas/verifikasi
     */
    public function index(): View
    {
        $this->assertGuru();

        $bkAccountId = auth()->user()->account_id;

        // Only agenda classrooms owned by this BK
        $agendaClassroomIds = \App\Models\Agenda::whereNotNull('classroom_id')
            ->whereHas('classroom', fn($q) => $q->where('bk_account_id', $bkAccountId))
            ->pluck('classroom_id');

        // Optional per-classroom filter via ?group=ID
        $filteredClassroom = null;
        $filterClassroomId = request('group');
        if ($filterClassroomId && $agendaClassroomIds->contains($filterClassroomId)) {
            $filteredClassroom  = Classroom::find($filterClassroomId);
            $filterIds          = collect([$filterClassroomId]);
        } else {
            $filterIds = $agendaClassroomIds;
        }

        $pendingRequests = ClassroomJoinRequest::with(['user', 'classroom'])
            ->whereIn('classroom_id', $filterIds)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $historyRequests = ClassroomJoinRequest::with(['user', 'classroom'])
            ->whereIn('classroom_id', $filterIds)
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('responded_at')
            ->limit(50)
            ->get();

        $pendingCount = $pendingRequests->count();

        // Agenda classrooms cards
        $agendaClassrooms = \App\Models\Agenda::with('classroom')
            ->whereNotNull('classroom_id')
            ->whereHas('classroom', fn($q) => $q->where('bk_account_id', $bkAccountId))
            ->latest()
            ->get()
            ->map(function ($agenda) use ($pendingRequests) {
                $classroom = $agenda->classroom;
                $agenda->pending_count  = $pendingRequests->where('classroom_id', $classroom?->id)->count();
                $agenda->approved_count = \App\Models\ClassroomJoinRequest::where('classroom_id', $classroom?->id)
                    ->where('status', 'approved')->count();
                return $agenda;
            });

        return view('users.pages.verifikasi-siswa', compact(
            'pendingRequests',
            'historyRequests',
            'pendingCount',
            'agendaClassrooms',
            'filteredClassroom',
        ));
    }

    /**
     * API: full feed for realtime polling.
     * GET /api/kelas/verifikasi/feed
     */
    public function feed(): JsonResponse
    {
        $this->assertGuru();

        $bkAccountId    = auth()->user()->account_id;
        $myClassroomIds = Classroom::where('bk_account_id', $bkAccountId)->pluck('id');

        $pending = ClassroomJoinRequest::with(['user', 'classroom'])
            ->whereIn('classroom_id', $myClassroomIds)
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id'         => $r->id,
                'name'       => $r->user->name        ?? '-',
                'loginId'    => $r->user->login_id    ?? '-',
                'classroom'  => $r->classroom->name   ?? '-',
                'reqAt'      => $r->created_at->format('d M Y, H:i'),
                'reqAtHuman' => $r->created_at->diffForHumans(),
            ]);

        $approvedCount = ClassroomJoinRequest::whereIn('classroom_id', $myClassroomIds)->where('status', 'approved')->count();
        $rejectedCount = ClassroomJoinRequest::whereIn('classroom_id', $myClassroomIds)->where('status', 'rejected')->count();

        return response()->json(compact('pending', 'approvedCount', 'rejectedCount'));
    }

    /**
     * API: count pending requests for this BK (used for badge).
     * GET /api/kelas/verifikasi/pending-count
     */
    public function pendingCount(): JsonResponse
    {
        $this->assertGuru();

        $bkAccountId    = auth()->user()->account_id;
        $myClassroomIds = Classroom::where('bk_account_id', $bkAccountId)->pluck('id');

        $count = ClassroomJoinRequest::whereIn('classroom_id', $myClassroomIds)
            ->where('status', 'pending')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Approve a join request.
     * POST /api/kelas/verifikasi/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        $this->assertGuru();

        $req = ClassroomJoinRequest::with(['user', 'classroom.dataKelas'])->findOrFail($id);
        $this->assertOwnsClassroom($req->classroom);

        abort_if($req->status !== 'pending', 422, 'Permintaan sudah diproses.');

        // Assign student to classroom; also auto-derive bk_id from the classroom's kelas
        $bkIdFromKelas = $req->classroom->dataKelas?->bk_id;
        $req->user->update(array_filter([
            'classroom_id' => $req->classroom_id,
            'bk_id'        => $bkIdFromKelas,
        ], fn($v) => $v !== null));

        // Increment stored student count on classroom
        $req->classroom->increment('students_count');

        // Update request status
        $req->update(['status' => 'approved', 'responded_at' => now()]);

        // System message in classroom chat.
        // Only skip if the user's last system message was already a "bergabung"
        // (prevents duplicates from double-approve, but allows re-join after leave).
        $lastSysMsg = ClassroomMessage::where('classroom_id', $req->classroom_id)
            ->where('user_id', $req->user_id)
            ->where('message_type', 'system')
            ->latest('id')
            ->value('message');
        $alreadyJoined = $lastSysMsg && str_contains($lastSysMsg, 'bergabung');
        if (!$alreadyJoined) {
            ClassroomMessage::create([
                'classroom_id' => $req->classroom_id,
                'user_id'      => $req->user_id,
                'message'      => $req->user->name . ' bergabung ke grup ini',
                'message_type' => 'system',
            ]);
        }

        // Notify student
        $notifBody = 'Permintaanmu bergabung ke kelas ' . ($req->classroom->name ?? 'kelas') . ' telah disetujui.';
        UserNotification::create([
            'user_id'      => $req->user_id,
            'type'         => 'kelas_approved',
            'title'        => 'Permintaan Bergabung Disetujui',
            'body'         => $notifBody,
            'related_type' => 'classroom:' . $req->classroom_id,
        ]);
        app(PushNotificationService::class)->sendToUser(
            $req->user_id,
            'Permintaan Bergabung Disetujui',
            $notifBody,
            ['url' => '/siswa/kelas']
        );

        return response()->json([
            'ok'      => true,
            'message' => $req->user->name . ' berhasil disetujui.',
        ]);
    }

    /**
     * Reject a join request.
     * POST /api/kelas/verifikasi/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $this->assertGuru();

        $req  = ClassroomJoinRequest::with(['user', 'classroom'])->findOrFail($id);
        $this->assertOwnsClassroom($req->classroom);

        abort_if($req->status !== 'pending', 422, 'Permintaan sudah diproses.');

        $note = $request->input('note');

        $req->update([
            'status'       => 'rejected',
            'note'         => $note,
            'responded_at' => now(),
        ]);

        // Notify student
        $notifBody = 'Permintaanmu bergabung ke kelas ' . ($req->classroom->name ?? 'kelas') . ' ditolak.'
            . ($note ? ' Alasan: ' . $note : '');
        UserNotification::create([
            'user_id'      => $req->user_id,
            'type'         => 'kelas_rejected',
            'title'        => 'Permintaan Bergabung Ditolak',
            'body'         => $notifBody,
            'related_type' => 'classroom:' . $req->classroom_id,
        ]);
        app(PushNotificationService::class)->sendToUser(
            $req->user_id,
            'Permintaan Bergabung Ditolak',
            $notifBody,
            ['url' => '/siswa/kelas']
        );

        return response()->json([
            'ok'      => true,
            'message' => 'Permintaan ' . $req->user->name . ' ditolak.',
        ]);
    }

    /** Ensure the authenticated BK owns the given classroom. */
    private function assertOwnsClassroom(Classroom $classroom): void
    {
        abort_unless(
            $classroom->bk_account_id === auth()->user()->account_id,
            403,
            'Kamu bukan pengelola kelas ini.'
        );
    }

    /**
     * Delete (hard-delete) a join request record.
     * DELETE /api/kelas/verifikasi/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->assertGuru();

        $req = ClassroomJoinRequest::with('classroom')->findOrFail($id);
        $this->assertOwnsClassroom($req->classroom);

        $req->delete();

        return response()->json(['ok' => true, 'message' => 'Berhasil dihapus.']);
    }
}
