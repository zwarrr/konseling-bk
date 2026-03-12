<?php

namespace App\Http\Controllers\Admin\Landing\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UploadsImage
{
    /**
     * Handle file upload. Returns the new public path, or $keep if no file was uploaded.
     */
    protected function uploadImg(Request $request, string $folder, string $prefix, ?string $keep = null): ?string
    {
        if ($request->hasFile('img') && $request->file('img')->isValid()) {
            if ($keep && str_starts_with($keep, '/storage/')) {
                Storage::disk('public')->delete(substr($keep, strlen('/storage/')));
            }
            $rand     = strtoupper(Str::random(4));
            $date     = now()->format('dmY');
            $ext      = $request->file('img')->getClientOriginalExtension();
            $filename = "IMG-{$prefix}-{$rand}-{$date}.{$ext}";
            $path     = $request->file('img')->storeAs("assets/img/{$folder}", $filename, 'public');
            return '/storage/' . $path;
        }
        return $keep;
    }

    /**
     * Parse accordion-style materi fields from the request.
     */
    protected function parseMateri(Request $request): array
    {
        $titles   = $request->input('materi_title', []);
        $allItems = $request->input('materi_items', []);
        $result   = [];
        foreach ($titles as $i => $t) {
            if (!trim($t)) continue;
            $rawItems = $allItems[$i] ?? '';
            $items    = array_filter(array_map('trim', explode("\n", $rawItems)));
            $result[] = ['title' => $t, 'items' => array_values($items)];
        }
        return $result;
    }
}
