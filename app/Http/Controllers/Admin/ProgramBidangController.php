<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramBidang;
use Illuminate\Http\Request;

class ProgramBidangController extends Controller
{
    public function index()
    {
        $bidangs = ProgramBidang::orderBy('name')->paginate(20);

        return view('admin.sections.kelola_data.program_kategori', compact('bidangs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60|unique:program_bidangs,name',
        ]);

        ProgramBidang::create([
            'name' => $data['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Kategori program berhasil ditambahkan.');
    }

    public function update(Request $request, ProgramBidang $programBidang)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60|unique:program_bidangs,name,' . $programBidang->id,
        ]);

        $oldName = $programBidang->name;
        $newName = $data['name'];
        $programBidang->update(['name' => $newName]);

        if ($oldName !== $newName) {
            Program::where('category', $oldName)->update(['category' => $newName]);
        }

        return back()->with('success', 'Kategori program berhasil diperbarui.');
    }

    public function toggle(ProgramBidang $programBidang)
    {
        $programBidang->update(['is_active' => !$programBidang->is_active]);

        return back()->with('success', 'Status kategori program berhasil diubah.');
    }

    public function destroy(ProgramBidang $programBidang)
    {
        $inUse = Program::where('category', $programBidang->name)->exists();
        if ($inUse) {
            return back()->with('error', 'Tidak bisa menghapus: kategori ini masih dipakai di program dan kegiatan.');
        }

        $programBidang->delete();

        return back()->with('success', 'Kategori program berhasil dihapus.');
    }
}
