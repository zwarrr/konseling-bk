<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramBidang;
use App\Models\ProgramReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProgramController extends Controller
{
    public function index(Request $request)
    {
        $activeBidang = trim((string) $request->query('bidang', ''));

        $programQuery = Program::query();
        if ($activeBidang !== '') {
            $programQuery->where('category', $activeBidang);
        }

        $programs = $programQuery
            ->orderByDesc('date')
            ->paginate(12)
            ->withQueryString();

        $bidangOptions = Program::query()
            ->select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('users.sections.program', compact('programs', 'bidangOptions', 'activeBidang'));
    }

    public function kelola()
    {
        abort_unless(auth()->user()->role === 'guru', 403);

        $programs = Program::where('added_by', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('users.pages.program-kelola', compact('programs'));
    }

    public function detail($slug)
    {
        $isGuru = (auth()->user()->role ?? '') === 'guru';
        $program = $isGuru
            ? Program::where('slug', $slug)->firstOrFail()
            : Program::where('slug', $slug)->firstOrFail();

        $reviews       = ProgramReview::where('program_id', $program->id)->latest()->paginate(5);
        $reviewsCount  = ProgramReview::where('program_id', $program->id)->count();
        $reviewsAvg    = (float) (ProgramReview::where('program_id', $program->id)->avg('rating') ?? 0);
        $firstReview   = ProgramReview::where('program_id', $program->id)->oldest()->first();
        $firstReviewId = $firstReview?->id;

        $allPrograms = Program::orderByDesc('date')->get();
        $isAuth = true;
        return view('shared.sections.program-detail', compact(
            'program', 'allPrograms', 'reviews', 'reviewsCount', 'reviewsAvg', 'firstReviewId', 'isAuth'
        ));
    }

    public function storeReview(Request $request, $slug)
    {
        $program = Program::where('slug', $slug)->firstOrFail();
        $request->validate([
            'name'    => 'required|string|max:100',
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);
        ProgramReview::create([
            'program_id' => $program->id,
            'name'      => $request->name,
            'rating'    => $request->rating,
            'comment'   => $request->comment,
        ]);
        return back()->with('review_success', 'Terima kasih! Ulasan Anda telah disimpan.');
    }

    // ── BK only ──────────────────────────────────────────────────────────

    public function create()
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $bidangs = ProgramBidang::active()->orderBy('name')->get(['id', 'name']);
        return view('users.partials.program-form', ['program' => null, 'bidangs' => $bidangs]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'guru', 403);

        $data = $request->validate([
            'category'    => 'required|string|max:60',
            'date'        => 'required|date',
            'title'       => 'required|string|max:50',
            'description' => 'nullable|string|max:2500',
            'benefits'    => 'nullable|array|max:12',
            'benefits.*'  => 'nullable|string|max:120',
            'info_link'   => 'nullable|url|max:500',
            'img'         => 'nullable|image|max:5120',
            'img_detail_1'=> 'nullable|image|max:5120',
            'img_detail_2'=> 'nullable|image|max:5120',
        ]);

        $benefits = $data['benefits'] ?? null;
        if (is_array($benefits)) {
            $benefits = array_values(array_filter(array_map(fn($v) => trim((string) $v), $benefits)));
            $benefits = array_slice($benefits, 0, 12);
        }
        $data['benefits'] = !empty($benefits) ? $benefits : null;

        $data['img']           = $this->uploadImg($request, 'img', null);
        $data['img_detail_1']  = $this->uploadImg($request, 'img_detail_1', null);
        $data['img_detail_2']  = $this->uploadImg($request, 'img_detail_2', null);
        $data['guru_pembimbing'] = null;
        $data['peserta']       = 0;
        $data['slug']          = $this->generateSlug($data['title']);
        $data['added_by']      = auth()->id();
        $data['info_link']     = $data['info_link'] ?? null;

        Program::create($data);

        return redirect()->route('bk.program.kelola')->with('success', 'Program dan kegiatan berhasil ditambahkan.');
    }

    public function edit($slug)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $program = Program::where('slug', $slug)->firstOrFail();
        $bidangs = ProgramBidang::active()->orderBy('name')->get(['id', 'name']);
        return view('users.partials.program-form', compact('program', 'bidangs'));
    }

    public function update(Request $request, $slug)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $program = Program::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'category'    => 'required|string|max:60',
            'date'        => 'required|date',
            'title'       => 'required|string|max:50',
            'description' => 'nullable|string|max:2500',
            'benefits'    => 'nullable|array|max:12',
            'benefits.*'  => 'nullable|string|max:120',
            'info_link'   => 'nullable|url|max:500',
            'img'         => 'nullable|image|max:5120',
            'img_detail_1'=> 'nullable|image|max:5120',
            'img_detail_2'=> 'nullable|image|max:5120',
        ]);

        $benefits = $data['benefits'] ?? null;
        if (is_array($benefits)) {
            $benefits = array_values(array_filter(array_map(fn($v) => trim((string) $v), $benefits)));
            $benefits = array_slice($benefits, 0, 12);
        }
        $data['benefits'] = !empty($benefits) ? $benefits : null;

        $data['img']          = $this->uploadImg($request, 'img', $program->img);
        $data['img_detail_1'] = $this->uploadImg($request, 'img_detail_1', $program->img_detail_1);
        $data['img_detail_2'] = $this->uploadImg($request, 'img_detail_2', $program->img_detail_2);
        $data['guru_pembimbing'] = null;
        $data['info_link']    = $data['info_link'] ?? null;
        if (!$program->slug) {
            $data['slug'] = $this->generateSlug($data['title'], $program->id);
        }

        $program->update($data);

        return redirect()->route('bk.program.kelola')->with('success', 'Program dan kegiatan berhasil diperbarui.');
    }

    public function destroy($slug)
    {
        abort_unless(auth()->user()->role === 'guru', 403);
        $program = Program::where('slug', $slug)->firstOrFail();
        $this->deleteImg($program->img);
        $program->delete();
        return redirect()->route('bk.program.kelola')->with('success', 'Program dan kegiatan berhasil dihapus.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function generateSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title) ?: Str::random(8);
        $slug = $base;
        $i    = 2;
        while (
            Program::where('slug', $slug)
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
            $filename = "IMG-PROGRAM-{$rand}-{$date}.{$ext}";
            $path     = $request->file($field)->storeAs('assets/img/img-program', $filename, 'public');
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
