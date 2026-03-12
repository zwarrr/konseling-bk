<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Models\AboutFeature;
use Illuminate\Http\Request;

class AboutFeatureController extends Controller
{
    private const MAX = 6;

    public function store(Request $request)
    {
        $feat = AboutFeature::count();
        if ($feat >= self::MAX) {
            return back()->with('feat_error', 'Maksimal ' . self::MAX . ' fitur unggulan.');
        }

        $data = $request->validate([
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
        ]);

        $data['sort_order'] = AboutFeature::max('sort_order') + 1;
        AboutFeature::create($data);

        return back()->with('feat_success', 'Fitur berhasil ditambahkan.');
    }

    public function update(Request $request, AboutFeature $feature)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
        ]);

        $feature->update($data);

        return back()->with('feat_success', 'Fitur berhasil diperbarui.');
    }

    public function destroy(AboutFeature $feature)
    {
        $feature->delete();
        return back()->with('feat_success', 'Fitur berhasil dihapus.');
    }
}
