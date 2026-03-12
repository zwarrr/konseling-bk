<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminKelasController;
use App\Http\Controllers\Admin\Landing\BkNewsController;
use App\Http\Controllers\Admin\Landing\TeamController;
use App\Http\Controllers\Admin\Landing\HomeSectionController;
use App\Http\Controllers\Admin\Landing\AboutSectionController;
use App\Http\Controllers\Admin\Landing\AboutFeatureController;
use App\Http\Controllers\Admin\Landing\ServiceSectionController;
use App\Http\Controllers\Admin\Landing\ServiceController;
use App\Http\Controllers\Admin\Landing\AgendaSectionController;
use App\Http\Controllers\Admin\Landing\AgendaController;

// ─── Admin (guard: admin) ──────────────────────────────────────────
Route::prefix('admin')->middleware(['auth:admin', 'admin'])->group(function () {
	// Root admin should redirect to a section URL
	Route::get('/', fn () => redirect()->route('admin.dashboard.index'))->name('admin.root');

	// Section: Dashboard
	Route::prefix('dashboard')->group(function () {
		Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard.index');
		Route::get('/stats', [AdminController::class, 'stats'])->name('admin.dashboard.stats');
		Route::get('/data', [AdminController::class, 'dashboardData'])->name('admin.dashboard.data');
	});

	// Section: Kelola
	Route::prefix('kelola')->group(function () {
		Route::get('/akun', [AdminController::class, 'accounts'])->name('admin.accounts.index');

		// BK accounts
		Route::post('/akun/bk',             [AdminController::class, 'storeBkAccount'])  ->name('admin.accounts.bk.store');
		Route::put('/akun/bk/{id}',         [AdminController::class, 'updateBkAccount']) ->name('admin.accounts.bk.update');
		Route::delete('/akun/bk/{id}',      [AdminController::class, 'deleteBkAccount']) ->name('admin.accounts.bk.delete');

		// Siswa/i accounts
		Route::post('/akun/siswa',          [AdminController::class, 'storeSiswaAccount'])  ->name('admin.accounts.siswa.store');
		Route::put('/akun/siswa/{id}',      [AdminController::class, 'updateSiswaAccount']) ->name('admin.accounts.siswa.update');
		Route::delete('/akun/siswa/{id}',   [AdminController::class, 'deleteSiswaAccount']) ->name('admin.accounts.siswa.delete');

		// Assign BK to Siswa
		Route::post('/akun/siswa/{id}/assign-bk', [AdminController::class, 'assignBk'])->name('admin.accounts.siswa.assignBk');

		// Import (Excel)
		Route::post('/akun/import',         [AdminController::class, 'importAccounts'])->name('admin.accounts.import');

		// Data Kelas
		Route::get('/data-kelas',              [AdminKelasController::class, 'index'])    ->name('admin.kelas.index');
		Route::post('/data-kelas',             [AdminKelasController::class, 'store'])    ->name('admin.kelas.store');
		Route::post('/data-kelas/import',      [AdminKelasController::class, 'importKelas'])->name('admin.kelas.import');
		Route::put('/data-kelas/{masterKela}',    [AdminKelasController::class, 'update'])  ->name('admin.kelas.update');
		Route::delete('/data-kelas/{masterKela}', [AdminKelasController::class, 'destroy']) ->name('admin.kelas.destroy');
	});

	// Backward-compatible route names
	Route::get('/legacy', fn () => redirect()->route('admin.dashboard.index'))->name('admin.dashboard');
	Route::get('/legacy/stats', fn () => redirect()->route('admin.dashboard.stats'))->name('admin.stats');
	Route::get('/legacy/accounts', fn () => redirect()->route('admin.accounts.index'))->name('admin.accounts');

	// ─── Landing Page CMS ─────────────────────────────────────────────
	Route::prefix('landing')->group(function () {
		// Overview — redirect to home section
		Route::get('/', fn () => redirect()->route('admin.landing.home'))->name('admin.landing.index');

		// Home Section (singleton)
		Route::get('/home',  [HomeSectionController::class, 'index']) ->name('admin.landing.home');
		Route::put('/home',  [HomeSectionController::class, 'update'])->name('admin.landing.homeUpdate');

		// About Section (singleton)
		Route::get('/about',  [AboutSectionController::class, 'index']) ->name('admin.landing.about');
		Route::put('/about',  [AboutSectionController::class, 'update'])->name('admin.landing.aboutUpdate');

		// About Features (CRUD, max 6)
		Route::post('/about/features',              [AboutFeatureController::class, 'store'])  ->name('admin.landing.featStore');
		Route::put('/about/features/{feature}',     [AboutFeatureController::class, 'update']) ->name('admin.landing.featUpdate');
		Route::delete('/about/features/{feature}',  [AboutFeatureController::class, 'destroy'])->name('admin.landing.featDestroy');

		// Service Section
		Route::get('/service',                    [ServiceSectionController::class, 'index']) ->name('admin.landing.service');
		Route::put('/service',                    [ServiceSectionController::class, 'update'])->name('admin.landing.serviceUpdate');
		Route::post('/service/items',             [ServiceController::class, 'store'])  ->name('admin.landing.serviceStore');
		Route::put('/service/items/{service}',    [ServiceController::class, 'update']) ->name('admin.landing.serviceItemUpdate');
		Route::delete('/service/items/{service}', [ServiceController::class, 'destroy'])->name('admin.landing.serviceDestroy');

		// Agenda Section
		Route::get('/agenda',                  [AgendaSectionController::class, 'index']) ->name('admin.landing.agenda');
		Route::put('/agenda',                  [AgendaSectionController::class, 'update'])->name('admin.landing.agendaUpdate');
		Route::post('/agenda/items',           [AgendaController::class, 'store'])  ->name('admin.landing.agendaStore');
		Route::put('/agenda/items/{agenda}',   [AgendaController::class, 'update']) ->name('admin.landing.agendaItemUpdate');
		Route::patch('/agenda/items/{agenda}/toggle', [AgendaController::class, 'toggle'])->name('admin.landing.agendaToggle');
		Route::delete('/agenda/items/{agenda}',[AgendaController::class, 'destroy'])->name('admin.landing.agendaDestroy');

		// BK News (replaces Sliders)
		Route::get('/bk-news',           [BkNewsController::class, 'index'])  ->name('admin.landing.bkNews');
		Route::post('/bk-news',          [BkNewsController::class, 'store'])  ->name('admin.landing.bkNewsStore');
		Route::put('/bk-news/{bkNews}',  [BkNewsController::class, 'update']) ->name('admin.landing.bkNewsUpdate');
		Route::delete('/bk-news/{bkNews}', [BkNewsController::class, 'destroy'])->name('admin.landing.bkNewsDestroy');
		Route::patch('/bk-news/{bkNews}/toggle', [BkNewsController::class, 'toggle'])->name('admin.landing.bkNewsToggle');

		// Team
		Route::get('/team', [TeamController::class, 'index'])->name('admin.landing.team');
		Route::post('/team', [TeamController::class, 'store'])->name('admin.landing.teamStore');
		Route::put('/team/{teamMember}', [TeamController::class, 'update'])->name('admin.landing.teamUpdate');
		Route::delete('/team/{teamMember}', [TeamController::class, 'destroy'])->name('admin.landing.teamDestroy');

	});

	// ─── System Settings ──────────────────────────────────────────────
	Route::get('/settings',                         [AdminController::class, 'settings'])                ->name('admin.settings');
	Route::post('/settings/maintenance/toggle',     [AdminController::class, 'toggleMaintenance'])       ->name('admin.settings.maintenance.toggle');
	Route::post('/settings/maintenance/message',    [AdminController::class, 'saveMaintenanceMessage'])  ->name('admin.settings.maintenance.message');
	Route::post('/settings/maintenance/admin-url',  [AdminController::class, 'saveMaintenanceAdminUrl']) ->name('admin.settings.maintenance.adminUrl');
});
