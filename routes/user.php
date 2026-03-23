<?php

/**
 * routes/user.php
 * ────────────────
 * All authenticated web-guard (student / guru) routes.
 * Required by routes/web.php.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserHomeController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\UserProgramController;
use App\Http\Controllers\User\UserNotificationController;
use App\Http\Controllers\User\UserChatController;
use App\Http\Controllers\User\UserKelasController;
use App\Http\Controllers\User\GroupChatController;
use App\Http\Controllers\Admin\AdminKelasController;
use App\Http\Controllers\User\SetupController;
use App\Http\Controllers\User\UserKelasVerifikasiController;
use App\Http\Controllers\User\UserProgramBookingController;
use App\Http\Controllers\User\UserBeritaController;

Route::middleware(['auth:bk,siswa', 'maintenance', 'must.setup'])->group(function () {

    // ─── First-time setup (unskippable) ────────────────────────────────────
    Route::get('/auth/setup',  [SetupController::class, 'show'])->name('user.setup')->withoutMiddleware(['must.setup', 'maintenance']);
    Route::post('/auth/setup', [SetupController::class, 'store'])->name('user.setup.store')->withoutMiddleware(['must.setup', 'maintenance']);

    // ─── Home ───────────────────────────────────────────────────────────────
    Route::get('/bk/home',    [UserHomeController::class, 'index'])->name('bk.home')->middleware('bk.only');
    Route::get('/siswa/home', [UserHomeController::class, 'index'])->name('siswa.home');

    // ─── Program ─────────────────────────────────────────────────────────
    Route::get('/bk/program',              [UserProgramController::class, 'index'])->name('bk.program');
    Route::get('/siswa/program',           [UserProgramController::class, 'index'])->name('siswa.program');

    // BK CRUD (must be before {slug} wildcard routes)
    Route::get('/bk/program/kelola',          [UserProgramController::class, 'kelola'])->name('bk.program.kelola')->middleware('bk.only');
    Route::get('/bk/program/create',          [UserProgramController::class, 'create'])->name('bk.program.create')->middleware('bk.only');
    Route::get('/bk/program/acc',             [UserKelasVerifikasiController::class, 'index'])->name('bk.program.acc')->middleware('bk.only');

    // Siswa: booking individu (date-time picker)
    Route::post('/siswa/program/{slug}/booking', [UserProgramBookingController::class, 'store'])->name('siswa.program.booking.store');

    Route::get('/bk/program/{slug}',       [UserProgramController::class, 'detail'])->name('bk.program.detail');
    Route::get('/siswa/program/{slug}',    [UserProgramController::class, 'detail'])->name('siswa.program.detail');

    // ─── Berita ───────────────────────────────────────────────────────────
    Route::get('/bk/berita',              [UserBeritaController::class, 'index']) ->name('bk.berita');
    Route::get('/siswa/berita',           [UserBeritaController::class, 'index']) ->name('siswa.berita');
    Route::get('/bk/berita/{slug}',       [UserBeritaController::class, 'detail'])->name('bk.berita.detail');
    Route::get('/siswa/berita/{slug}',    [UserBeritaController::class, 'detail'])->name('siswa.berita.detail');
    Route::post('/bk/program/{slug}/reviews',   [UserProgramController::class, 'storeReview'])->name('bk.program.reviews.store');
    Route::post('/siswa/program/{slug}/reviews',[UserProgramController::class, 'storeReview'])->name('siswa.program.reviews.store');
    Route::post('/bk/program',                 [UserProgramController::class, 'store'])->name('bk.program.store');
    Route::get('/bk/program/{slug}/edit',      [UserProgramController::class, 'edit'])->name('bk.program.edit');
    Route::put('/bk/program/{slug}',           [UserProgramController::class, 'update'])->name('bk.program.update');
    Route::delete('/bk/program/{slug}',        [UserProgramController::class, 'destroy'])->name('bk.program.destroy');

    // ─── Profile ─────────────────────────────────────────────────────────
    Route::get('/bk/profile',    [UserProfileController::class, 'show'])->name('bk.profile');
    Route::get('/siswa/profile', [UserProfileController::class, 'show'])->name('siswa.profile');
    Route::get('/profile', function () {
        $role = auth()->user()->role ?? null;
        return $role === 'guru'
            ? redirect()->route('bk.profile')
            : redirect()->route('siswa.profile');
    })->name('profile.page');

    // Profile update API
    Route::post('/api/profile/update',                [UserProfileController::class, 'update'])->name('profile.update');
    Route::post('/api/profile/force-change-password', [UserProfileController::class, 'forceChangePassword'])->name('profile.forceChangePassword');

    // ─── Notifications ───────────────────────────────────────────────────
    Route::get('/bk/notifikasi',    [UserNotificationController::class, 'index'])->name('bk.notifikasi');
    Route::get('/siswa/notifikasi', [UserNotificationController::class, 'index'])->name('siswa.notifikasi');

    // Notification API
    Route::get('/api/notif/count',           [UserNotificationController::class, 'count'])->name('notif.count');
    Route::post('/api/notif/read/{id}',      [UserNotificationController::class, 'markRead'])->name('notif.read');
    Route::post('/api/notif/read-all',       [UserNotificationController::class, 'markAllRead'])->name('notif.readAll');

    // Web Push subscription endpoint
    Route::post('/api/push/subscribe', [UserNotificationController::class, 'subscribe'])->name('push.subscribe');

    // ─── Kelola Kelas (BK only) ───────────────────────────────────────
    Route::get('/bk/kelas',                                  [UserKelasController::class, 'index'])->name('bk.kelas')->middleware('bk.only');
    Route::get('/bk/kelas/{id}/kelompok',                    [UserKelasController::class, 'kelompok'])->name('bk.kelas.kelompok')->middleware('bk.only');
    Route::get('/api/kelas/unassigned-count',                [UserKelasController::class, 'unassignedCount'])->name('kelas.unassigned-count');
    Route::get('/api/classes',                              [AdminKelasController::class, 'listJson'])->name('classes.list');
    Route::post('/api/kelas',                                [UserKelasController::class, 'store'])->name('kelas.store');
    Route::put('/api/kelas/{id}',                            [UserKelasController::class, 'update'])->name('kelas.update');
    Route::delete('/api/kelas/{id}',                         [UserKelasController::class, 'destroy'])->name('kelas.destroy');
    Route::get('/api/kelas/{id}/students',                   [UserKelasController::class, 'students'])->name('kelas.students');
    Route::post('/api/kelas/{id}/students/assign',           [UserKelasController::class, 'assignStudent'])->name('kelas.assign');
    Route::post('/api/kelas/{id}/students/remove',           [UserKelasController::class, 'removeStudent'])->name('kelas.remove');

    // ─── Kelompok Kelas (BK only) ────────────────────────────────────────
    Route::get('/api/kelas/{id}/groups',                     [UserKelasController::class, 'groups'])->name('kelas.groups.index');
    Route::post('/api/kelas/{id}/groups',                    [UserKelasController::class, 'storeGroup'])->name('kelas.groups.store');
    Route::put('/api/kelas/{id}/groups/{gid}',               [UserKelasController::class, 'updateGroup'])->name('kelas.groups.update');
    Route::patch('/api/kelas/{id}/groups/{gid}/toggle',      [UserKelasController::class, 'toggleGroup'])->name('kelas.groups.toggle');
    Route::delete('/api/kelas/{id}/groups/{gid}',            [UserKelasController::class, 'destroyGroup'])->name('kelas.groups.destroy');


    // ─── Program Booking Verifikasi (BK only) ───────────────────────────
    Route::get('/api/program/booking/pending-count', [UserProgramBookingController::class, 'pendingCount'])->name('program.booking.pendingCount');
    Route::post('/api/program/booking/{id}/approve', [UserProgramBookingController::class, 'approve'])->name('program.booking.approve');
    Route::post('/api/program/booking/{id}/reject',  [UserProgramBookingController::class, 'reject'])->name('program.booking.reject');

    // ─── Program Booking (Siswa actions) ───────────────────────────────
    Route::post('/api/program/booking/{id}/chat-reconfirm', [UserProgramBookingController::class, 'chatReconfirm'])->name('program.booking.chatReconfirm');

    // ─── Kelas Group Chat ────────────────────────────────────────────────
    Route::get('/kelas/{slug}/chat',                         [GroupChatController::class, 'room'])->name('kelas.chat.room');
    Route::get('/api/kelas/{id}/chat/members',               [GroupChatController::class, 'members'])->name('kelas.chat.members');
    Route::get('/api/kelas/{id}/chat/messages',              [GroupChatController::class, 'messages'])->name('kelas.chat.messages');
    Route::post('/api/kelas/{id}/chat/send',                 [GroupChatController::class, 'send'])->name('kelas.chat.send');
    Route::post('/api/kelas/{id}/chat/send-media',           [GroupChatController::class, 'sendMedia'])->name('kelas.chat.sendMedia');
    // Camera page for kelas (mobile)
    Route::get('/kelas/{id}/camera', function ($id) {
        abort_unless(\Auth::guard('bk')->check() || \Auth::guard('siswa')->check(), 403);
        $kelas = \App\Models\Classroom::findOrFail($id);
        return view('shared.sections.camera', [
            'roomId'  => $id,
            'backUrl' => route('kelas.chat.room', $kelas->slug),
        ]);
    })->name('kelas.camera');
    Route::post('/api/kelas/{id}/chat/mark-read',            [GroupChatController::class, 'markRead'])->name('kelas.chat.markRead');

    // ─── Chat ─────────────────────────────────────────────────────────────
    Route::get('/bk/chat',    [UserChatController::class, 'index'])->name('bk.chat');
    Route::get('/siswa/chat', [UserChatController::class, 'siswaIndex'])->name('siswa.chat');

    // Get-or-create room with a guru (siswa initiates)
    Route::get('/chat/start/{guruId}', [UserChatController::class, 'startOrOpen'])->name('chat.start');

    // Chat room page
    Route::get('/chat/room/{roomId}', [UserChatController::class, 'room'])->name('chat.room');

    // Camera page (mobile)
    Route::get('/chat/camera/{roomId}', function ($roomId) {
        abort_unless(\Auth::guard('bk')->check() || \Auth::guard('siswa')->check(), 403);
        return view('shared.sections.camera', ['roomId' => $roomId]);
    })->name('chat.camera');

    // Chat API (polling + send)
    Route::get('/api/chat/conversations',        [UserChatController::class, 'conversationsJson'])->name('chat.conversations');
    Route::get('/api/chat/{roomId}/messages',   [UserChatController::class, 'messages'])->name('chat.messages');
    Route::post('/api/chat/{roomId}/send',       [UserChatController::class, 'send'])->name('chat.send');
    Route::post('/api/chat/{roomId}/send-media', [UserChatController::class, 'sendMedia'])->name('chat.sendMedia');
    Route::post('/api/chat/delete-conversations',[UserChatController::class, 'deleteConversations'])->name('chat.deleteConversations');

});

// ─── Admin maintenance bypass ──────────────────────────────────────────────
// Accessed via /auth/{secret} — sets session bypass flag and shows login page.
// This route is intentionally outside all auth/maintenance middleware groups.
Route::get('/auth/{bypass}', function (\Illuminate\Http\Request $request, string $bypass) {
    $secret = trim(\App\Models\AppSetting::get('maintenance_admin_url', 'ginlogin'), '/ ');
    if ($secret !== '' && $bypass === $secret) {
        session(['maintenance_bypass' => true]);
        return app(\App\Http\Controllers\AuthController::class)->showLogin($request);
    }
    abort(404);
})->where('bypass', '[a-zA-Z0-9\-_]+')->name('auth.admin.bypass');
