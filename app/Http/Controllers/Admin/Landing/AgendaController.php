<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AgendaController extends Controller
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
            'status'          => 'required|in:draft,publish',
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

        // Auto-create classroom group
        if ($request->boolean('auto_group') && empty($data['classroom_id'])) {
            $classroom = \App\Models\Classroom::create([
                'name'        => $data['title'],
                'description' => 'Grup Agenda: ' . $data['title'],
            ]);
            $data['classroom_id'] = $classroom->id;
        }

        Agenda::create($data);

        return back()->with('agenda_success', 'Agenda berhasil ditambahkan.');
    }

    public function update(Request $request, Agenda $agenda)
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
            'status'          => 'required|in:draft,publish',
            'peserta'         => 'nullable|integer|min:0',
            'guru_pembimbing' => 'nullable|string|max:100',
        ]);

        $data['img']          = $this->uploadImgFile($request, 'img',          $agenda->img);
        $data['img_detail_1'] = $this->uploadImgFile($request, 'img_detail_1', $agenda->img_detail_1);
        $data['img_detail_2'] = $this->uploadImgFile($request, 'img_detail_2', $agenda->img_detail_2);
        $data['info_link']    = $data['info_link'] ?? null;
        $data['peserta']      = $data['peserta'] ?? 0;

        // Auto-create classroom group (only if not already linked)
        if ($request->boolean('auto_group') && empty($agenda->classroom_id)) {
            $classroom = \App\Models\Classroom::create([
                'name'        => $data['title'],
                'description' => 'Grup Agenda: ' . $data['title'],
            ]);
            $data['classroom_id'] = $classroom->id;
        }

        // Ensure slug exists (e.g. for rows created before slug was added)
        if (!$agenda->slug) {
            $data['slug'] = $this->generateSlug($data['title'], $agenda->id);
        }

        $agenda->update($data);

        return back()->with('agenda_success', 'Agenda berhasil diperbarui.');
    }

    public function toggle(Agenda $agenda)
    {
        $agenda->update([
            'status' => $agenda->status === 'publish' ? 'draft' : 'publish',
        ]);

        return back()->with('agenda_success', 'Status agenda berhasil diubah.');
    }

    public function destroy(Agenda $agenda)
    {
        foreach (['img', 'img_detail_1', 'img_detail_2'] as $col) {
            $this->deleteStorageFile($agenda->$col);
        }
        $agenda->delete();

        return back()->with('agenda_success', 'Agenda berhasil dihapus.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function generateSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title);
        if (!$base) $base = Str::random(8);
        $slug = $base;
        $i    = 2;
        while (
            Agenda::where('slug', $slug)
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
            $filename = "IMG-AGENDA-{$rand}-{$date}.{$ext}";
            $path     = $request->file($field)->storeAs('assets/img/img-agenda', $filename, 'public');
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
