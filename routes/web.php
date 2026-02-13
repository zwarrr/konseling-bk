<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PresenceController;
use App\Models\User;

// Web login
Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('auth.login');

// Admin login (same form; separate endpoint so admin can login alongside web in 1 browser)
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');

// Laravel default auth middleware expects a route named 'login'
Route::get('/login', fn () => redirect()->route('auth.login'))->name('login');

// Allow submitting login even when already authenticated (switch account)
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.loginSubmit');

// Web logout (keeps admin login if any)
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('auth.logout');

// Admin logout (keeps web login if any)
Route::post('/admin/logout', [AuthController::class, 'logoutAdmin'])->middleware('auth:admin')->name('admin.logout');

// Root: go to whichever session is active
Route::get('/', function () {
	if (\Illuminate\Support\Facades\Auth::guard('web')->check()) {
		$role = \Illuminate\Support\Facades\Auth::guard('web')->user()?->role;
		return ($role === 'guru')
			? redirect()->route('bk.chat')
			: redirect()->route('siswa.chat');
	}

	if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
		return redirect()->route('admin.dashboard.index');
	}

	return redirect()->route('auth.login');
});

// ─── Admin (guard: admin) ──────────────────────────────────────────
Route::prefix('admin')->middleware(['auth:admin', 'admin'])->group(function () {
	// Root admin should redirect to a section URL
	Route::get('/', fn () => redirect()->route('admin.dashboard.index'))->name('admin.root');

	// Section: Dashboard
	Route::prefix('dashboard')->group(function () {
		Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard.index');
		Route::get('/stats', [AdminController::class, 'stats'])->name('admin.dashboard.stats');
	});

	// Section: Kelola
	Route::prefix('kelola')->group(function () {
		Route::get('/akun', [AdminController::class, 'accounts'])->name('admin.accounts.index');
		Route::get('/akun/create', [AdminController::class, 'createAccount'])->name('admin.accounts.create');
		Route::post('/akun', [AdminController::class, 'storeAccount'])->name('admin.accounts.store');
		Route::get('/akun/{user}/edit', [AdminController::class, 'editAccount'])->name('admin.accounts.edit');
		Route::put('/akun/{user}', [AdminController::class, 'updateAccount'])->name('admin.accounts.update');
		Route::delete('/akun/{user}', [AdminController::class, 'deleteAccount'])->name('admin.accounts.delete');
	});

	// Backward-compatible URLs
	Route::get('/stats', fn () => redirect()->route('admin.dashboard.stats'));
	Route::get('/accounts', fn () => redirect()->route('admin.accounts.index'));
	Route::get('/accounts/create', fn () => redirect()->route('admin.accounts.create'));
	Route::get('/accounts/{user}/edit', fn (User $user) => redirect()->route('admin.accounts.edit', $user));

	// Backward-compatible route names
	Route::get('/legacy', fn () => redirect()->route('admin.dashboard.index'))->name('admin.dashboard');
	Route::get('/legacy/stats', fn () => redirect()->route('admin.dashboard.stats'))->name('admin.stats');
	Route::get('/legacy/accounts', fn () => redirect()->route('admin.accounts.index'))->name('admin.accounts');
});

// ─── Web authenticated (guard: web) ─────────────────────────────────
Route::middleware('auth')->group(function () {

	// Role-specific chat entrypoints
	Route::get('/bk/chat', [ChatController::class, 'bkChat'])->name('bk.chat');
	Route::get('/siswa/chat', [ChatController::class, 'siswaChat'])->name('siswa.chat');

	Route::get('/bk/room/{roomId?}', [ChatController::class, 'room'])->name('bk.room');
	Route::get('/siswa/room/{roomId?}', [ChatController::class, 'room'])->name('siswa.room');

	// Main chat page
	Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
	Route::get('/chat/list', [ChatController::class, 'index'])->name('chat.list'); // backward compat

// Documentation page


// Chat room page
	Route::get('/room/{roomId?}', [ChatController::class, 'room'])->name('chat.room');
	Route::get('/chatroom', [ChatController::class, 'room'])->name('chatroom'); // Alias untuk compatibility

// API endpoints for chat functionality
	Route::post('/api/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
	Route::get('/api/chat/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
	Route::get('/api/chat/messages/{apiRef}', [ChatController::class, 'getMessageStatus'])->name('chat.messageStatus');
	Route::get('/api/chat/sessions', [ChatController::class, 'getSessions'])->name('chat.sessions');
	Route::post('/api/presence/ping', [PresenceController::class, 'ping'])->name('presence.ping');
	Route::get('/api/presence/status', [PresenceController::class, 'status'])->name('presence.status');
	Route::post('/api/chat/typing', [ChatController::class, 'typing'])->name('chat.typing');
	Route::post('/api/chat/new-session', [ChatController::class, 'newSession'])->name('chat.newSession');
	Route::post('/api/chat/end-session', [ChatController::class, 'endSession'])->name('chat.endSession');
});
