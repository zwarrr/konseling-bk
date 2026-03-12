<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use App\Models\AboutFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AboutSectionController extends Controller
{
    public function index()
    {
        return view('admin.sections.landingpage_sections.about', [
            'about'    => AboutSection::singleton(),
            'features' => AboutFeature::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:120',
            'subtitle'    => 'required|string|max:120',
            'description' => 'nullable|string|max:600',
            'img_1'       => 'nullable|image|max:5120',
            'img_2'       => 'nullable|image|max:5120',
            'img_3'       => 'nullable|image|max:5120',
            'img_4'       => 'nullable|image|max:5120',
        ]);

        $about = AboutSection::singleton();

        $data = $request->only(['title', 'subtitle', 'description']);

        foreach (['img_1', 'img_2', 'img_3', 'img_4'] as $field) {
            $data[$field] = $this->uploadField($request, $field, $about->$field);
        }

        $about->update($data);

        return back()->with('success', 'Section Tentang berhasil diperbarui.');
    }

    private function uploadField(Request $request, string $field, ?string $keep): ?string
    {
        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            if ($keep && str_starts_with($keep, '/storage/')) {
                Storage::disk('public')->delete(substr($keep, strlen('/storage/')));
            }
            $num      = strtoupper(substr($field, -1));
            $rand     = strtoupper(Str::random(4));
            $date     = now()->format('dmY');
            $ext      = $request->file($field)->getClientOriginalExtension();
            $filename = "IMG-ABOUT{$num}-{$rand}-{$date}.{$ext}";
            $path     = $request->file($field)->storeAs('assets/img/img-about', $filename, 'public');
            return '/storage/' . $path;
        }
        return $keep;
    }
}
