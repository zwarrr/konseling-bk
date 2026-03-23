<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Admin\Landing\Concerns\UploadsImage;
use App\Http\Controllers\Controller;
use App\Models\ProfileBkGallery;
use App\Models\ProfileBkSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileBkController extends Controller
{
    use UploadsImage;

    public function index()
    {
        return view('admin.sections.landingpage_sections.profile_bk', [
            'section' => ProfileBkSection::singleton(),
            'items'   => ProfileBkGallery::orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }

    public function updateSection(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:150',
            'subtitle'    => 'nullable|string|max:150',
            'description' => 'nullable|string|max:1200',
            'vision'      => 'nullable|string|max:1200',
            'mission'     => 'nullable|string|max:2000',
            'mission_lines' => 'nullable|array|max:12',
            'mission_lines.*' => 'nullable|string|max:120',
            'img'         => 'nullable|image|max:5120',
        ]);

        $section = ProfileBkSection::singleton();

        $uploaded = $this->uploadImg($request, 'img-profile-bk', 'PROFILE-BK', $section->img);
        $img      = $uploaded ?? $section->img;

        $missionLines = collect($request->input('mission_lines', []))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->take(12)
            ->all();

        $mission = count($missionLines)
            ? implode("\n", $missionLines)
            : $request->input('mission');

        $section->update([
            'title'       => $request->input('title'),
            'subtitle'    => $request->input('subtitle'),
            'description' => $request->input('description'),
            'vision'      => $request->input('vision'),
            'mission'     => $mission,
            'img'         => $img,
        ]);

        return back()->with('success', 'Profil BK berhasil diperbarui.');
    }

    public function storeGallery(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:120',
            'description' => 'nullable|string|max:300',
            'sort_order'  => 'nullable|integer|min:0|max:9999',
            'img'         => 'nullable|image|max:5120',
        ]);

        $img = $this->uploadImg($request, 'img-galery-bk', 'GALERY-BK', null);

        ProfileBkGallery::create([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'sort_order'  => (int) ($request->input('sort_order') ?? 0),
            'img'         => $img,
        ]);

        return back()->with('success', 'Item galeri berhasil ditambahkan.');
    }

    public function updateGallery(Request $request, ProfileBkGallery $item)
    {
        $request->validate([
            'title'       => 'required|string|max:120',
            'description' => 'nullable|string|max:300',
            'sort_order'  => 'nullable|integer|min:0|max:9999',
            'img'         => 'nullable|image|max:5120',
        ]);

        $uploaded = $this->uploadImg($request, 'img-galery-bk', 'GALERY-BK', $item->img);
        $img      = $uploaded ?? $item->img;

        $item->update([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'sort_order'  => (int) ($request->input('sort_order') ?? 0),
            'img'         => $img,
        ]);

        return back()->with('success', 'Item galeri berhasil diperbarui.');
    }

    public function destroyGallery(ProfileBkGallery $item)
    {
        if ($item->img && str_starts_with($item->img, '/storage/')) {
            Storage::disk('public')->delete(substr($item->img, strlen('/storage/')));
        }

        $item->delete();

        return back()->with('success', 'Item galeri berhasil dihapus.');
    }
}
