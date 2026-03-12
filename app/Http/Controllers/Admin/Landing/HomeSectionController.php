<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Landing\Concerns\UploadsImage;
use App\Models\HomeSection;
use Illuminate\Http\Request;

class HomeSectionController extends Controller
{
    use UploadsImage;

    public function index()
    {
        return view('admin.sections.landingpage_sections.home', [
            'home' => HomeSection::singleton(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:120',
            'subtitle'    => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'img'         => 'nullable|image|max:5120',
        ]);

        $home = HomeSection::singleton();

        $uploaded = $this->uploadImg($request, 'img-home', 'HOME', $home->img);
        $img      = $uploaded ?? $home->img;

        $home->update([
            'title'       => $request->input('title'),
            'subtitle'    => $request->input('subtitle'),
            'description' => $request->input('description'),
            'img'         => $img,
        ]);

        return back()->with('success', 'Section beranda berhasil diperbarui.');
    }
}
