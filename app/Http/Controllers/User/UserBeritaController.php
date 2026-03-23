<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BkNews;

class UserBeritaController extends Controller
{
    public function index()
    {
        $news = BkNews::published()->paginate(6);
        return view('users.sections.berita', compact('news'));
    }

    public function detail($slug)
    {
        $item    = BkNews::published()->where('slug', $slug)->firstOrFail();
        $related = BkNews::published()->where('id', '!=', $item->id)->take(4)->get();
        return view('users.pages.berita-detail', compact('item', 'related'));
    }
}
