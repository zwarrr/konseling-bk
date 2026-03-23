<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'img'             => 'nullable|image|max:5120',
            'img_detail_1'    => 'nullable|image|max:5120',
            'img_detail_2'    => 'nullable|image|max:5120',
            'category'        => 'required|string|max:60',
            'date'            => 'required|date',
            'title'           => 'required|string|max:50',
            'description'     => 'nullable|string|max:2500',
            'info_link'       => 'nullable|url|max:500',
            'peserta'         => 'nullable|integer|min:0',
            'guru_pembimbing' => 'nullable|string|max:100',
        ]);

        $data['img']          = $this->uploadImgFile($request, 'img',          null);
        $data['img_detail_1'] = $this->uploadImgFile($request, 'img_detail_1', null);
        $data['img_detail_2'] = $this->uploadImgFile($request, 'img_detail_2', null);
        $data['peserta']      = $data['peserta'] ?? 0;
        $data['slug']         = $this->generateSlug($data['title']);
        $data['added_by']     = auth('admin')->id();
        $data['info_link']    = $data['info_link'] ?? null;

        Program::create($data);

        return back()->with('program_success', 'Program dan kegiatan berhasil ditambahkan.');
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'img'             => 'nullable|image|max:5120',
            'img_detail_1'    => 'nullable|image|max:5120',
            'img_detail_2'    => 'nullable|image|max:5120',
            'category'        => 'required|string|max:60',
            'date'            => 'required|date',
            'title'           => 'required|string|max:50',
            'description'     => 'nullable|string|max:2500',
            'info_link'       => 'nullable|url|max:500',
            'peserta'         => 'nullable|integer|min:0',
            'guru_pembimbing' => 'nullable|string|max:100',
        ]);

        $data['img']          = $this->uploadImgFile($request, 'img',          $program->img);
        $data['img_detail_1'] = $this->uploadImgFile($request, 'img_detail_1', $program->img_detail_1);
        $data['img_detail_2'] = $this->uploadImgFile($request, 'img_detail_2', $program->img_detail_2);
        $data['info_link']    = $data['info_link'] ?? null;
        $data['peserta']      = $data['peserta'] ?? 0;

        // Ensure slug exists (e.g. for rows created before slug was added)
        if (!$program->slug) {
            $data['slug'] = $this->generateSlug($data['title'], $program->id);
        }

        $program->update($data);

        return back()->with('program_success', 'Program dan kegiatan berhasil diperbarui.');
    }

    public function destroy(Program $program)
    {
        foreach (['img', 'img_detail_1', 'img_detail_2'] as $col) {
            $this->deleteStorageFile($program->$col);
        }
        $program->delete();

        return back()->with('program_success', 'Program dan kegiatan berhasil dihapus.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function generateSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title);
        if (!$base) $base = Str::random(8);
        $slug = $base;
        $i    = 2;
        while (
            Program::where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $base . $i++;
        }
        return $slug;
    }

    private function uploadImgFile(Request $request, string $field, ?string $keep): ?string
    {
        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            $this->deleteStorageFile($keep);
            $rand     = strtoupper(Str::random(6));
            $date     = now()->format('dmY');
            $ext      = $request->file($field)->getClientOriginalExtension();
            $filename = "IMG-PROGRAM-{$rand}-{$date}.{$ext}";
            $path     = $request->file($field)->storeAs('assets/img/img-program', $filename, 'public');
            return '/storage/' . $path;
        }
        return $keep;
    }

    private function deleteStorageFile(?string $path): void
    {
        if ($path && str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('/storage/')));
        }
    }
}
