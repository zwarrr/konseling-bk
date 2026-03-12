<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\AgendaGroup;
use App\Models\AgendaReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserAgendaController extends Controller
{
    public function index()
    {
        $agendas = Agenda::where('status', 'publish')
            ->orderByDesc('date')
            ->paginate(12);

        return view('users.sections.agenda', compact('agendas'));
    }

    public function kelola()
    {
        abort_unless(auth()->user()->role === 'guru', 403);

        $agendas = Agenda::where('added_by', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('users.pages.agenda-kelola', compact('agendas'));
    }

    public function detail($slug)
    {
        $isGuru = (auth()->user()->role ?? '') === 'guru';
        $agenda = $isGuru
            ? Agenda::where('slug', $slug)->firstOrFail()
            : Agenda::where('status', 'publish')->where('slug', $slug)->firstOrFail();

        $reviews       = AgendaReview::where('agenda_id', $agenda->id)->latest()->paginate(5);
        $reviewsCount  = AgendaReview::where('agenda_id', $agenda->id)->count();
        $reviewsAvg    = (float) (AgendaReview::where('agenda_id', $agenda->id)->avg('rating') ?? 0);
        $firstReview   = AgendaReview::where('agenda_id', $agenda->id)->oldest()->first();
        $firstReviewId = $firstReview?->id;

        $allAgendas = Agenda::where('status', 'publish')->orderByDesc('date')->get();
        $isAuth = true;
        return view('shared.sections.agenda-detail', compact(
            'agenda', 'allAgendas', 'reviews', 'reviewsCount', 'reviewsAvg', 'firstReviewId', 'isAuth'
        ));
    }

    public function storeReview(Request $request, $slug)
    {
        $agenda = Agenda::where('slug', $slug)->firstOrFail();
        $request->validate([
            'name'    => 'required|string|max:100',
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);
        AgendaReview::create([
            'agenda_id' => $agenda->id,
            'name'      => $request->name,
            'rating'    => $request->rating,
            'comment'   => $request->comment,
        ]);
        return back()->with('review_success', 'Terima kasih! Ulasan Anda telah disimpan.');
    }

    // ── BK only ──────────────────────────────────────────────────────────

    public function grup($slug)
    {
        abort_unless(auth()->user()->role === 'guru', 403);

        $agenda = Agenda::where('slug', $slug)
            ->where('added_by', auth()->id())
            ->firstOrFail();

        abort_if(is_null($agenda->classroom_id), 404, 'Agenda ini tidak memiliki grup chat.');

        $classroom = $agenda->classroom()->firstOrFail();

        $groups = AgendaGroup::where('agenda_id', $agenda->id)->orderBy('id')->get();

        return view('users.pages.agenda-grup', compact('agenda', 'classroom', 'groups'));
    }

    // ── Agenda Group CRUD API ─────────────────────────────────────────────

    public function agendaGroupsIndex($classroomId)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $groups = AgendaGroup::where('classroom_id', $classroomId)->orderBy('id')->get();
        return response()->json(['groups' => $groups]);
    }

    public function agendaGroupsStore(Request $request, $classroomId)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $data = $request->validate(['name' => 'required|string|max:100']);
        $group = AgendaGroup::create([
            'classroom_id' => $classroomId,
            'name'         => $data['name'],
            'is_active'    => false,
        ]);
        return response()->json(['group' => $group]);
    }

    public function agendaGroupsUpdate(Request $request, $classroomId, $gid)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $group = AgendaGroup::where('classroom_id', $classroomId)->findOrFail($gid);
        $data  = $request->validate(['name' => 'required|string|max:100']);
        $group->update(['name' => $data['name']]);
        return response()->json(['group' => $group]);
    }

    public function agendaGroupsToggle($classroomId, $gid)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $group = AgendaGroup::where('classroom_id', $classroomId)->findOrFail($gid);
        $group->update(['is_active' => !$group->is_active]);
        return response()->json(['group' => $group]);
    }

    public function agendaGroupsDestroy($classroomId, $gid)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $group = AgendaGroup::where('classroom_id', $classroomId)->findOrFail($gid);
        $group->delete();
        return response()->json(['ok' => true]);
    }

    public function create()
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        return view('users.partials.agenda-form', ['agenda' => null]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'guru', 403);

        $data = $request->validate([
            'category'    => 'required|string|max:60',
            'date'        => 'required|date',
            'title'       => 'required|string|max:50',
            'description' => 'nullable|string|max:2500',
            'info_link'   => 'nullable|url|max:500',
            'status'      => 'required|in:draft,publish',
            'img'         => 'nullable|image|max:5120',
            'img_detail_1'=> 'nullable|image|max:5120',
            'img_detail_2'=> 'nullable|image|max:5120',
        ]);

        $data['img']           = $this->uploadImg($request, 'img', null);
        $data['img_detail_1']  = $this->uploadImg($request, 'img_detail_1', null);
        $data['img_detail_2']  = $this->uploadImg($request, 'img_detail_2', null);
        $data['guru_pembimbing'] = null;
        $data['peserta']       = 0;
        $data['slug']          = $this->generateSlug($data['title']);
        $data['added_by']      = auth()->id();
        $data['info_link']     = $data['info_link'] ?? null;

        // Auto-create classroom group with AGR prefix (distinct from KLS kelas)
        if ($request->boolean('auto_group')) {
            $lastAgr = \App\Models\Classroom::where('id', 'like', 'AGR%')
                ->orderByRaw('CAST(SUBSTRING(id, 4) AS UNSIGNED) DESC')
                ->value('id');
            $nextNum = $lastAgr ? ((int) substr($lastAgr, 3)) + 1 : 1;
            $agrId   = 'AGR' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);

            $classroom = \App\Models\Classroom::create([
                'id'          => $agrId,
                'name'        => $data['title'],
                'description' => 'Grup Agenda: ' . $data['title'],
            ]);
            $data['classroom_id'] = $classroom->id;
        }

        Agenda::create($data);

        return redirect()->route('bk.agenda.kelola')->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function edit($slug)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $agenda = Agenda::where('slug', $slug)->firstOrFail();
        return view('users.partials.agenda-form', compact('agenda'));
    }

    public function update(Request $request, $slug)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $agenda = Agenda::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'category'    => 'required|string|max:60',
            'date'        => 'required|date',
            'title'       => 'required|string|max:50',
            'description' => 'nullable|string|max:2500',
            'info_link'   => 'nullable|url|max:500',
            'status'      => 'required|in:draft,publish',
            'img'         => 'nullable|image|max:5120',
            'img_detail_1'=> 'nullable|image|max:5120',
            'img_detail_2'=> 'nullable|image|max:5120',
        ]);

        $data['img']          = $this->uploadImg($request, 'img', $agenda->img);
        $data['img_detail_1'] = $this->uploadImg($request, 'img_detail_1', $agenda->img_detail_1);
        $data['img_detail_2'] = $this->uploadImg($request, 'img_detail_2', $agenda->img_detail_2);
        $data['guru_pembimbing'] = null;
        $data['info_link']    = $data['info_link'] ?? null;
        if (!$agenda->slug) {
            $data['slug'] = $this->generateSlug($data['title'], $agenda->id);
        }

        // Auto-create classroom group with AGR prefix (only if not already linked)
        if ($request->boolean('auto_group') && empty($agenda->classroom_id)) {
            $lastAgr = \App\Models\Classroom::where('id', 'like', 'AGR%')
                ->orderByRaw('CAST(SUBSTRING(id, 4) AS UNSIGNED) DESC')
                ->value('id');
            $nextNum = $lastAgr ? ((int) substr($lastAgr, 3)) + 1 : 1;
            $agrId   = 'AGR' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);

            $classroom = \App\Models\Classroom::create([
                'id'          => $agrId,
                'name'        => $data['title'],
                'description' => 'Grup Agenda: ' . $data['title'],
            ]);
            $data['classroom_id'] = $classroom->id;
        }

        $agenda->update($data);

        return redirect()->route('bk.agenda.kelola')->with('success', 'Agenda berhasil diperbarui.');
    }

    public function toggle($slug)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $agenda = Agenda::where('slug', $slug)->firstOrFail();
        $agenda->update(['status' => $agenda->status === 'publish' ? 'draft' : 'publish']);
        return back()->with('success', 'Status agenda diubah.');
    }

    public function destroy($slug)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $agenda = Agenda::where('slug', $slug)->firstOrFail();
        $this->deleteImg($agenda->img);
        $agenda->delete();
        return redirect()->route('bk.agenda.kelola')->with('success', 'Agenda berhasil dihapus.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function generateSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title) ?: Str::random(8);
        $slug = $base;
        $i    = 2;
        while (
            Agenda::where('slug', $slug)
                ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function uploadImg(Request $request, string $field, ?string $keep): ?string
    {
        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            $this->deleteImg($keep);
            $rand     = strtoupper(Str::random(6));
            $date     = now()->format('dmY');
            $ext      = $request->file($field)->getClientOriginalExtension();
            $filename = "IMG-AGENDA-{$rand}-{$date}.{$ext}";
            $path     = $request->file($field)->storeAs('assets/img/img-agenda', $filename, 'public');
            return '/storage/' . $path;
        }
        return $keep;
    }

    private function deleteImg(?string $path): void
    {
        if ($path && str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('/storage/')));
        }
    }
}
