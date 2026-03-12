<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Models\ServiceSection;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceSectionController extends Controller
{
    public function index()
    {
        return view('admin.sections.landingpage_sections.service', [
            'section'  => ServiceSection::singleton(),
            'services' => Service::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:150',
            'subtitle'    => 'nullable|string|max:150',
            'description' => 'nullable|string|max:600',
            'img'         => 'nullable|image|max:5120',
        ]);

        $section = ServiceSection::singleton();

        $data = $request->only(['title', 'subtitle', 'description']);
        $data['img'] = $this->uploadImg($request, $section->img);

        $section->update($data);

        return back()->with('success', 'Section Layanan berhasil diperbarui.');
    }

    private function uploadImg(Request $request, ?string $keep): ?string
    {
        if ($request->hasFile('img') && $request->file('img')->isValid()) {
            if ($keep && str_starts_with($keep, '/storage/')) {
                Storage::disk('public')->delete(substr($keep, strlen('/storage/')));
            }
            $rand     = strtoupper(Str::random(6));
            $date     = now()->format('dmY');
            $ext      = $request->file('img')->getClientOriginalExtension();
            $filename = "IMG-SERVICE-{$rand}-{$date}.{$ext}";
            $path     = $request->file('img')->storeAs('assets/img/img-service', $filename, 'public');
            return '/storage/' . $path;
        }
        return $keep;
    }
}
