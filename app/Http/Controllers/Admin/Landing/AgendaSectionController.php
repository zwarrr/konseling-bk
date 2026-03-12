<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Models\AgendaSection;
use App\Models\Agenda;
use Illuminate\Http\Request;

class AgendaSectionController extends Controller
{
    public function index()
    {
        return view('admin.sections.landingpage_sections.agenda', [
            'section' => AgendaSection::singleton(),
            'agendas' => Agenda::orderBy('date', 'desc')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string|max:600',
        ]);

        $section = AgendaSection::singleton();
        $section->update($request->only(['title', 'description']));

        return back()->with('success', 'Header Agenda berhasil diperbarui.');
    }
}
