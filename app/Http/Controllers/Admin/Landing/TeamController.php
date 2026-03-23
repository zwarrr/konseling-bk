<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Landing\Concerns\UploadsImage;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    use UploadsImage;

    public function index()
    {
        return view('admin.sections.team', [
            'items' => TeamMember::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'quote' => 'nullable|string|max:255',
            'img'   => 'nullable|image|max:5120',
        ]);
        $uploaded         = $this->uploadImg($request, 'img-team', 'TEAM');
        $validated['img'] = $uploaded ?? null;
        TeamMember::create($validated);
        return back()->with('success', 'Tim BK diperbarui.');
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'quote' => 'nullable|string|max:255',
            'img'   => 'nullable|image|max:5120',
        ]);
        $uploaded         = $this->uploadImg($request, 'img-team', 'TEAM', $teamMember->img);
        $validated['img'] = $uploaded ?? $teamMember->img;
        $teamMember->update($validated);
        return back()->with('success', 'Tim BK diperbarui.');
    }

    public function destroy(TeamMember $teamMember)
    {
        $teamMember->delete();
        return back()->with('success', 'Tim BK diperbarui.');
    }
}
