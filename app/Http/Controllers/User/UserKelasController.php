<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BkAccount;
use App\Models\Classroom;
use App\Models\ClassroomGroup;
use App\Models\ClassroomJoinRequest;
use App\Models\ClassroomMessage;
use App\Models\Kelas;
use App\Models\SiswaAccount;
use App\Services\KelasSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserKelasController extends Controller
{
    /** Guru BK only — guard. */
    private function assertGuru(): void
    {
        abort_unless(auth()->user()?->role === 'guru', 403);
    }

    /**
     * Main page: list all classrooms.
     */
    public function index(): View
    {
        $this->assertGuru();

        $bkAccountId = auth()->user()->account_id;

        // Exclude agenda classrooms — those are managed via the agenda/verifikasi page
        $agendaClassroomIds = \App\Models\Agenda::whereNotNull('classroom_id')->pluck('classroom_id');

        $classrooms = Classroom::with('dataKelas')
            ->withCount('students')
            ->where('bk_account_id', $bkAccountId)
            ->whereNotIn('id', $agendaClassroomIds)
            ->orderBy('name')->get();

        $totalEnrolled = $classrooms->sum('students_count');
        $belumDiKelas  = SiswaAccount::where('bk_id', auth()->user()->id)
            ->whereNull('classroom_id')
            ->count();

        return view('users.sections.kelas', compact('classrooms', 'totalEnrolled', 'belumDiKelas'));
    }

    /**
     * Dedicated kelompok management page.
     */
    public function kelompok(string $id): View
    {
        $this->assertGuru();

        $kelas = Classroom::with(['dataKelas', 'groups'])
            ->withCount('students')
            ->findOrFail($id);
        abort_if($kelas->bk_account_id !== auth()->user()->account_id, 403);

        // Agenda classrooms have no absen-based groups — redirect to dedicated page
        $agenda = \App\Models\Agenda::where('classroom_id', $id)->first();
        if ($agenda) {
            return redirect()->route('bk.agenda.grup', $agenda->slug);
        }

        $students = SiswaAccount::where('classroom_id', $id)
            ->orderBy('absen')->orderBy('name')
            ->get(['id', 'name', 'account_id', 'login_id', 'absen']);

        return view('users.sections.kelompok', compact('kelas', 'students'));
    }

    /**
     * Create a classroom.
     */
    public function store(Request $request): JsonResponse
    {
        $this->assertGuru();

        $data = $request->validate([
            'class_id'    => 'required|integer|exists:classes,id',
            'description' => 'nullable|string|max:500',
        ]);

        $master = Kelas::findOrFail($data['class_id']);
        $name   = strtoupper($master->kelas) . ' ' . $master->jurusan;

        $kelas = Classroom::create([
            'name'          => $name,
            'class_id'      => $master->id,
            'bk_account_id' => auth()->user()->account_id,
            'description'   => $data['description'] ?? null,
        ]);

        // Sync Kelas.bk_id ↑ and students' bk_id ↓
        KelasSync::fromClassroom(
            auth()->user()->account_id,
            $master->id,
            $kelas->id,
        );

        return response()->json([
            'kelas'          => $kelas,
            'students_count' => 0,
        ], 201);
    }

    /**
     * Update a classroom.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $this->assertGuru();

        $kelas = Classroom::findOrFail($id);
        abort_if($kelas->bk_account_id !== auth()->user()->account_id, 403, 'Kamu bukan pengelola kelas ini.');

        $data = $request->validate([
            'class_id'    => 'nullable|integer|exists:classes,id',
            'description' => 'nullable|string|max:500',
        ]);

        $updates = ['description' => $data['description'] ?? null];

        if (!empty($data['class_id'])) {
            $master = Kelas::findOrFail($data['class_id']);
            $updates['name']     = strtoupper($master->kelas) . ' ' . $master->jurusan;
            $updates['class_id'] = $master->id;
        }

        $kelas->update($updates);

        // Sync Kelas.bk_id ↑ and students' bk_id ↓
        KelasSync::fromClassroom(
            $kelas->fresh()->bk_account_id,
            $kelas->fresh()->class_id,
            $kelas->id,
        );

        return response()->json(['kelas' => $kelas->fresh()]);
    }

    /**
     * Delete a classroom (students become unassigned).
     */
    public function destroy(string $id): JsonResponse
    {
        $this->assertGuru();

        $kelas = Classroom::findOrFail($id);
        abort_if($kelas->bk_account_id !== auth()->user()->account_id, 403, 'Kamu bukan pengelola kelas ini.');
        // Nullify students' classroom_id and bk_id (cascade done below)
        SiswaAccount::where('classroom_id', $id)->update(['classroom_id' => null, 'bk_id' => null]);

        $classId = $kelas->class_id;
        $kelas->delete();

        // Clear Kelas.bk_id if no other classroom still links to it
        if ($classId && !Classroom::where('class_id', $classId)->exists()) {
            KelasSync::fromClassroom(null, $classId, $id);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * JSON: list students currently enrolled in a classroom.
     */
    public function students(string $id): JsonResponse
    {
        $this->assertGuru();

        $kelas = Classroom::findOrFail($id);
        abort_if($kelas->bk_account_id !== auth()->user()->account_id, 403, 'Kamu bukan pengelola kelas ini.');

        $members = SiswaAccount::where('classroom_id', $id)
                       ->orderBy('absen')
                       ->orderBy('name')
                       ->get(['id', 'name', 'account_id', 'login_id', 'classroom_id', 'absen']);

        return response()->json([
            'members' => $members,
        ]);
    }

    /**
     * Assign a student to this classroom.
     */
    public function assignStudent(Request $request, string $id): JsonResponse
    {
        $this->assertGuru();
        $kelas = Classroom::findOrFail($id);
        abort_if($kelas->bk_account_id !== auth()->user()->account_id, 403, 'Kamu bukan pengelola kelas ini.');

        $data    = $request->validate(['student_id' => 'required|integer|exists:siswa_i_account,id']);
        $student = SiswaAccount::findOrFail($data['student_id']);
        $student->update(['classroom_id' => $id]);
        // bk_id synced by SiswaAccountObserver

        return response()->json(['student' => $student->only(['id', 'name', 'account_id', 'login_id'])]);
    }

    /**
     * JSON: count of siswa who have no classroom yet.
     */
    public function unassignedCount(): JsonResponse
    {
        $this->assertGuru();
        $count = SiswaAccount::whereNull('classroom_id')->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Remove (unassign) a student from this classroom.
     */
    public function removeStudent(Request $request, string $id): JsonResponse
    {
        $this->assertGuru();
        $kelas = Classroom::findOrFail($id);
        abort_if($kelas->bk_account_id !== auth()->user()->account_id, 403, 'Kamu bukan pengelola kelas ini.');

        $data    = $request->validate(['student_id' => 'required|integer|exists:siswa_i_account,id']);
        $student = SiswaAccount::where('id', $data['student_id'])
                       ->where('classroom_id', $id)
                       ->firstOrFail();
        $student->update(['classroom_id' => null]);
        Classroom::where('id', $id)->decrement('students_count');

        return response()->json(['ok' => true]);
    }

    /**
     * Siswa leaves their own classroom.
     * POST /api/kelas/{id}/leave
     */
    public function leaveKelas(string $id): JsonResponse
    {
        $user = auth()->user();
        abort_if($user->role === 'guru', 403, 'Guru tidak dapat keluar dari kelas.');

        $kelas = Classroom::findOrFail($id);

        // Primary classroom member
        if ($user->classroom_id == $kelas->id) {
            $user->update(['classroom_id' => null]);
            $kelas->decrement('students_count');

            ClassroomMessage::create([
                'classroom_id' => $kelas->id,
                'user_id'      => $user->id,
                'message'      => $user->name . ' keluar dari grup ini',
                'message_type' => 'system',
            ]);

            return response()->json(['ok' => true]);
        }

        // Agenda kelas: member joined via ClassroomJoinRequest
        $joinReq = ClassroomJoinRequest::where('user_id', $user->id)
            ->where('classroom_id', $kelas->id)
            ->where('status', 'approved')
            ->first();

        abort_unless($joinReq, 403, 'Kamu bukan anggota kelas ini.');

        $joinReq->delete();

        ClassroomMessage::create([
            'classroom_id' => $kelas->id,
            'user_id'      => $user->id,
            'message'      => $user->name . ' keluar dari grup ini',
            'message_type' => 'system',
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Kelompok (sub-groups) ────────────────────────────────────────

    /**
     * List groups for a classroom.
     * GET /api/kelas/{id}/groups
     */
    public function groups(string $id): JsonResponse
    {
        $this->assertGuru();
        $kelas = Classroom::findOrFail($id);
        abort_if($kelas->bk_account_id !== auth()->user()->account_id, 403);

        return response()->json([
            'groups' => $kelas->groups()->orderBy('id')->get(),
        ]);
    }

    /**
     * Create a new group.
     * POST /api/kelas/{id}/groups
     */
    public function storeGroup(Request $request, string $id): JsonResponse
    {
        $this->assertGuru();
        $kelas = Classroom::findOrFail($id);
        abort_if($kelas->bk_account_id !== auth()->user()->account_id, 403);

        $isAgenda = \App\Models\Agenda::where('classroom_id', $id)->exists();

        $data = $request->validate($isAgenda
            ? ['name' => 'required|string|max:100']
            : [
                'name'       => 'required|string|max:100',
                'absen_from' => 'required|integer|min:1',
                'absen_to'   => 'required|integer|min:1|gte:absen_from',
            ]
        );

        $group = ClassroomGroup::create([
            'classroom_id' => $id,
            'name'         => $data['name'],
            'absen_from'   => $data['absen_from'] ?? null,
            'absen_to'     => $data['absen_to'] ?? null,
            'is_active'    => false,
        ]);

        return response()->json(['group' => $group], 201);
    }

    /**
     * Update a group's name / NIS range.
     * PUT /api/kelas/{id}/groups/{gid}
     */
    public function updateGroup(Request $request, string $id, int $gid): JsonResponse
    {
        $this->assertGuru();
        $group = ClassroomGroup::where('id', $gid)->where('classroom_id', $id)->firstOrFail();
        abort_if($group->classroom->bk_account_id !== auth()->user()->account_id, 403);

        $data = $request->validate([
            'name'       => 'sometimes|string|max:100',
            'absen_from' => 'sometimes|integer|min:1',
            'absen_to'   => 'sometimes|integer|min:1',
        ]);

        $group->update($data);

        return response()->json(['group' => $group->fresh()]);
    }

    /**
     * Toggle the active status of a group.
     * PATCH /api/kelas/{id}/groups/{gid}/toggle
     */
    public function toggleGroup(string $id, int $gid): JsonResponse
    {
        $this->assertGuru();
        $group = ClassroomGroup::where('id', $gid)->where('classroom_id', $id)->firstOrFail();
        abort_if($group->classroom->bk_account_id !== auth()->user()->account_id, 403);

        $group->update(['is_active' => !$group->is_active]);

        return response()->json(['group' => $group->fresh()]);
    }

    /**
     * Delete a group.
     * DELETE /api/kelas/{id}/groups/{gid}
     */
    public function destroyGroup(string $id, int $gid): JsonResponse
    {
        $this->assertGuru();
        $group = ClassroomGroup::where('id', $gid)->where('classroom_id', $id)->firstOrFail();
        abort_if($group->classroom->bk_account_id !== auth()->user()->account_id, 403);

        $group->delete();

        return response()->json(['ok' => true]);
    }
}