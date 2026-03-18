<?php

/**
 * routes/public.php
 * ──────────────────
 * Publicly accessible routes: landing page, auth, program detail.
 * Required by routes/web.php.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// ─── Auth ────────────────────────────────────────────────────────────
Route::middleware(['maintenance'])->group(function () {
    Route::get('/auth/onboarding', fn () => view('auth.onboarding'))->name('auth.onboarding');
    Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.loginSubmit');
});
Route::get('/admin/login', fn () => redirect()->route('auth.login'))->name('admin.login');
Route::get('/login', fn () => redirect()->route('auth.onboarding'))->name('login');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:bk,siswa')->name('auth.logout');
Route::post('/admin/logout', [AuthController::class, 'logoutAdmin'])->middleware('auth:admin')->name('admin.logout');

// ─── Landing root ─────────────────────────────────────────────────────
Route::get('/', function () {
    return view('frontend.landingpage.landingpage');
})->name('landing');

// ─── Landing sub-pages ────────────────────────────────────────────────
Route::get('/kontak', fn () => view('frontend.landingpage.sections.contact'))->name('landing.contact');
Route::get('/tim-bk', fn () => view('frontend.landingpage.sections.team'))->name('landing.team');
Route::get('/copyright-team', fn () => view('frontend.landingpage.sections.copyright_team'))
    ->name('landing.copyright_team');

Route::get('/berita', function () {
    $news = \App\Models\BkNews::published()->paginate(9);
    return view('frontend.landingpage.sections.berita', compact('news'));
})->name('landing.berita');

Route::get('/berita/{slug}', function ($slug) {
    $item    = \App\Models\BkNews::published()->where('slug', $slug)->firstOrFail();
    $related = \App\Models\BkNews::published()->where('id', '!=', $item->id)->take(4)->get();
    return view('frontend.landingpage.pages.berita-detail', compact('item', 'related'));
})->where('slug', '[a-z0-9\-]+')->name('landing.berita.detail');

use App\Models\ProgramReview;

Route::get('/program/{slug}', function ($slug) {
    $program        = \App\Models\Program::where('status', 'publish')->where('slug', $slug)->firstOrFail();
    $allPrograms    = \App\Models\Program::where('status', 'publish')->orderByDesc('date')->get();
    $reviews        = ProgramReview::where('program_id', $program->id)->latest()->paginate(5);
    $reviewsCount   = ProgramReview::where('program_id', $program->id)->count();
    $reviewsAvg     = (float) (ProgramReview::where('program_id', $program->id)->avg('rating') ?? 0);
    $firstReview    = ProgramReview::where('program_id', $program->id)->oldest()->first();
    $firstReviewId = $firstReview?->id;
    $isAuth = false;
    return view('shared.sections.program-detail', compact(
        'program', 'allPrograms', 'reviews', 'reviewsCount', 'reviewsAvg', 'firstReviewId', 'isAuth'
    ));
})->name('landing.program.detail');

Route::post('/program/{slug}/reviews', function ($slug, \Illuminate\Http\Request $request) {
    $program = \App\Models\Program::where('status', 'publish')->where('slug', $slug)->firstOrFail();
    $request->validate([
        'name'    => 'required|string|max:100',
        'rating'  => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:500',
    ]);
    ProgramReview::create([
        'program_id' => $program->id,
        'name'      => $request->name,
        'rating'    => $request->rating,
        'comment'   => $request->comment,
    ]);
    return back()->with('review_success', 'Terima kasih! Ulasan Anda telah disimpan.');
})->name('landing.program.reviews.store');

// Section scroll aliases — same landing view, JS scrolls on load
Route::get('/{section}', fn () => view('frontend.landingpage.landingpage'))
    ->where('section', 'beranda|slider-news-bk|tentang|statistik|layanan|program|cta')
    ->name('landing.section');
