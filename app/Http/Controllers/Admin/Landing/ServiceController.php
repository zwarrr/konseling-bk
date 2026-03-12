<?php

namespace App\Http\Controllers\Admin\Landing;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    private const MAX = 6;

    public function store(Request $request)
    {
        if (Service::count() >= self::MAX) {
            return back()->with('svc_error', 'Maksimal ' . self::MAX . ' layanan.');
        }

        $data = $request->validate([
            'icon'        => 'required|string|max:80',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
        ]);

        $data['sort_order'] = Service::max('sort_order') + 1;
        Service::create($data);

        return back()->with('svc_success', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'icon'        => 'required|string|max:80',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
        ]);

        $service->update($data);

        return back()->with('svc_success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('svc_success', 'Layanan berhasil dihapus.');
    }
}
