<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Models\ProgramSection;
use App\Models\Program;
use App\Models\ProgramBidang;
use Illuminate\Http\Request;

class ProgramSectionController extends Controller
{
    public function index()
    {
        return view('admin.sections.landingpage_sections.program', [
            'section' => ProgramSection::singleton(),
            'programs' => Program::orderBy('date', 'desc')->get(),
            'bidangs' => ProgramBidang::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string|max:600',
        ]);

        $section = ProgramSection::singleton();
        $section->update($request->only(['title', 'description']));

        return back()->with('success', 'Header Program dan Kegiatan berhasil diperbarui.');
    }
}
