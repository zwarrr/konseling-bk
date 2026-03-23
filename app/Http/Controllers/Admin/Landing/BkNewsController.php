<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Landing\Concerns\UploadsImage;
use App\Models\BkNews;
use Illuminate\Http\Request;

class BkNewsController extends Controller
{
    use UploadsImage;

    public function index()
    {
        return view('admin.sections.bknews', [
            'items' => BkNews::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:50',
            'description'  => 'nullable|string|max:2500',
            'author'       => 'nullable|string|max:100',
            'img_cards'    => 'nullable|image|max:5120',
            'img_detail_1' => 'nullable|image|max:5120',
            'img_detail_2' => 'nullable|image|max:5120',
        ]);

        $title = $request->input('title');

        BkNews::create([
            'title'        => $title,
            'slug'         => BkNews::generateUniqueSlug($title),
            'description'  => $request->input('description'),
            'author'       => $request->input('author'),
            'img_cards'    => $this->uploadImgField($request, 'img_cards', 'NEWS-CARDS'),
            'img_detail_1' => $this->uploadImgField($request, 'img_detail_1', 'NEWS-D1'),
            'img_detail_2' => $this->uploadImgField($request, 'img_detail_2', 'NEWS-D2'),
        ]);

        return back()->with('success', 'Berita ditambahkan.');
    }

    public function update(Request $request, BkNews $bkNews)
    {
        $request->validate([
            'title'        => 'required|string|max:50',
            'description'  => 'nullable|string|max:2500',
            'author'       => 'nullable|string|max:100',
            'img_cards'    => 'nullable|image|max:5120',
            'img_detail_1' => 'nullable|image|max:5120',
            'img_detail_2' => 'nullable|image|max:5120',
        ]);

        $newTitle = $request->input('title');
        // Regenerate slug only if title changed and slug has not been manually set
        $newSlug = ($bkNews->title !== $newTitle)
            ? BkNews::generateUniqueSlug($newTitle, $bkNews->id)
            : $bkNews->slug;

        $bkNews->update([
            'title'        => $newTitle,
            'slug'         => $newSlug,
            'description'  => $request->input('description'),
            'author'       => $request->input('author'),
            'img_cards'    => $this->uploadImgField($request, 'img_cards', 'NEWS-CARDS', $bkNews->img_cards)
                              ?? $bkNews->img_cards,
            'img_detail_1' => $this->uploadImgField($request, 'img_detail_1', 'NEWS-D1', $bkNews->img_detail_1)
                              ?? $bkNews->img_detail_1,
            'img_detail_2' => $this->uploadImgField($request, 'img_detail_2', 'NEWS-D2', $bkNews->img_detail_2)
                              ?? $bkNews->img_detail_2,
        ]);

        return back()->with('success', 'Berita diperbarui.');
    }

    public function destroy(BkNews $bkNews)
    {
        $bkNews->delete();
        return back()->with('success', 'Berita dihapus.');
    }

    /** Upload helper that maps a named file input (not always 'img'). */
    protected function uploadImgField(Request $request, string $field, string $prefix, ?string $keep = null): ?string
    {
        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            if ($keep && str_starts_with($keep, '/storage/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(substr($keep, strlen('/storage/')));
            }
            $rand     = strtoupper(\Illuminate\Support\Str::random(4));
            $date     = now()->format('dmY');
            $ext      = $request->file($field)->getClientOriginalExtension();
            $filename = "IMG-{$prefix}-{$rand}-{$date}.{$ext}";
            $path     = $request->file($field)->storeAs('assets/img/img-news', $filename, 'public');
            return '/storage/' . $path;
        }
        return $keep;
    }
}
