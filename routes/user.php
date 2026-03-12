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
use App\Http\Controllers\User\UserAgendaController;
use App\Http\Controllers\User\UserNotificationController;
use App\Http\Controllers\User\UserChatController;
use App\Http\Controllers\User\UserKelasController;
use App\Http\Controllers\User\ClassroomChatController;
use App\Http\Controllers\Admin\AdminKelasController;
use App\Http\Controllers\User\SetupController;
use App\Http\Controllers\User\UserKelasVerifikasiController;
use App\Http\Controllers\User\UserBeritaController;

Route::middleware(['auth:bk,siswa', 'maintenance', 'must.setup'])->group(function () {

    // ─── First-time setup (unskippable) ────────────────────────────────────
    Route::get('/auth/setup',  [SetupController::class, 'show'])->name('user.setup')->withoutMiddleware(['must.setup', 'maintenance']);
    Route::post('/auth/setup', [SetupController::class, 'store'])->name('user.setup.store')->withoutMiddleware(['must.setup', 'maintenance']);

    // ─── Home ───────────────────────────────────────────────────────────────
    Route::get('/bk/home',    [UserHomeController::class, 'index'])->name('bk.home')->middleware('bk.only');
    Route::get('/siswa/home', [UserHomeController::class, 'index'])->name('siswa.home');

    // ─── Agenda ──────────────────────────────────────────────────────────
    Route::get('/bk/agenda',              [UserAgendaController::class, 'index'])->name('bk.agenda');
    Route::get('/siswa/agenda',           [UserAgendaController::class, 'index'])->name('siswa.agenda');

    // BK CRUD (must be before {slug} wildcard routes)
    Route::get('/bk/agenda/kelola',          [UserAgendaController::class, 'kelola'])->name('bk.agenda.kelola')->middleware('bk.only');
    Route::get('/bk/agenda-create',          [UserAgendaController::class, 'create'])->name('bk.agenda.create')->middleware('bk.only');
    Route::get('/bk/agenda/verifikasi',      [UserKelasVerifikasiController::class, 'index'])->name('bk.agenda.verifikasi')->middleware('bk.only');
    Route::get('/bk/agenda/{slug}/grup',     [UserAgendaController::class, 'grup'])->name('bk.agenda.grup')->middleware('bk.only');

    Route::get('/bk/agenda/{slug}',       [UserAgendaController::class, 'detail'])->name('bk.agenda.detail');
    Route::get('/siswa/agenda/{slug}',    [UserAgendaController::class, 'detail'])->name('siswa.agenda.detail');

    // ─── Berita ───────────────────────────────────────────────────────────
    Route::get('/bk/berita',              [UserBeritaController::class, 'index']) ->name('bk.berita');
    Route::get('/siswa/berita',           [UserBeritaController::class, 'index']) ->name('siswa.berita');
    Route::get('/bk/berita/{slug}',       [UserBeritaController::class, 'detail'])->name('bk.berita.detail');
    Route::get('/siswa/berita/{slug}',    [UserBeritaController::class, 'detail'])->name('siswa.berita.detail');
    Route::post('/bk/agenda/{slug}/reviews',   [UserAgendaController::class, 'storeReview'])->name('bk.agenda.reviews.store');
    Route::post('/siswa/agenda/{slug}/reviews',[UserAgendaController::class, 'storeReview'])->name('siswa.agenda.reviews.store');
    Route::post('/bk/agenda',                [UserAgendaController::class, 'store'])->name('bk.agenda.store');
    Route::get('/bk/agenda/{slug}/edit',     [UserAgendaController::class, 'edit'])->name('bk.agenda.edit');
    Route::put('/bk/agenda/{slug}',          [UserAgendaController::class, 'update'])->name('bk.agenda.update');
    Route::patch('/bk/agenda/{slug}/toggle', [UserAgendaController::class, 'toggle'])->name('bk.agenda.toggle');
    Route::delete('/bk/agenda/{slug}',       [UserAgendaController::class, 'destroy'])->name('bk.agenda.destroy');

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
    Route::post('/api/kelas/{id}/leave',                     [UserKelasController::class, 'leaveKelas'])->name('kelas.leave');

    // ─── Kelompok Kelas (BK only) ────────────────────────────────────────
    Route::get('/api/kelas/{id}/groups',                     [UserKelasController::class, 'groups'])->name('kelas.groups.index');
    Route::post('/api/kelas/{id}/groups',                    [UserKelasController::class, 'storeGroup'])->name('kelas.groups.store');
    Route::put('/api/kelas/{id}/groups/{gid}',               [UserKelasController::class, 'updateGroup'])->name('kelas.groups.update');
    Route::patch('/api/kelas/{id}/groups/{gid}/toggle',      [UserKelasController::class, 'toggleGroup'])->name('kelas.groups.toggle');
    Route::delete('/api/kelas/{id}/groups/{gid}',            [UserKelasController::class, 'destroyGroup'])->name('kelas.groups.destroy');

    // ─── Kelompok Agenda (agenda_groups table, keyed by agendas.id) ─────
    Route::get('/api/agenda-groups/{agendaId}',                [UserAgendaController::class, 'agendaGroupsIndex'])->name('agenda.groups.index');
    Route::post('/api/agenda-groups/{agendaId}',               [UserAgendaController::class, 'agendaGroupsStore'])->name('agenda.groups.store');
    Route::put('/api/agenda-groups/{agendaId}/{gid}',          [UserAgendaController::class, 'agendaGroupsUpdate'])->name('agenda.groups.update');
    Route::patch('/api/agenda-groups/{agendaId}/{gid}/toggle', [UserAgendaController::class, 'agendaGroupsToggle'])->name('agenda.groups.toggle');
    Route::delete('/api/agenda-groups/{agendaId}/{gid}',       [UserAgendaController::class, 'agendaGroupsDestroy'])->name('agenda.groups.destroy');

    // ─── Kelas Verifikasi Siswa (BK only) ────────────────────────────────
    Route::get('/api/kelas/verifikasi/feed',                   [UserKelasVerifikasiController::class, 'feed'])->name('kelas.verifikasi.feed');
    Route::get('/api/kelas/verifikasi/pending-count',          [UserKelasVerifikasiController::class, 'pendingCount'])->name('kelas.verifikasi.pendingCount');
    Route::post('/api/kelas/verifikasi/{id}/approve',          [UserKelasVerifikasiController::class, 'approve'])->name('kelas.verifikasi.approve');
    Route::post('/api/kelas/verifikasi/{id}/reject',           [UserKelasVerifikasiController::class, 'reject'])->name('kelas.verifikasi.reject');
    Route::delete('/api/kelas/verifikasi/{id}',                [UserKelasVerifikasiController::class, 'destroy'])->name('kelas.verifikasi.destroy');

    // ─── Kelas Group Chat ────────────────────────────────────────────────
    Route::get('/kelas/join/{token}',                        [ClassroomChatController::class, 'joinPage'])->name('kelas.join.page');
    Route::post('/kelas/join/{token}',                       [ClassroomChatController::class, 'joinConfirm'])->name('kelas.join.confirm');
    Route::get('/api/kelas/join/status',                     [ClassroomChatController::class, 'joinStatus'])->name('kelas.join.status');
    Route::get('/kelas/{slug}/chat',                         [ClassroomChatController::class, 'room'])->name('kelas.chat.room');
    Route::get('/api/kelas/{id}/chat/members',             [ClassroomChatController::class, 'members'])->name('kelas.chat.members');
    Route::get('/api/kelas/{id}/chat/messages',              [ClassroomChatController::class, 'messages'])->name('kelas.chat.messages');
    Route::post('/api/kelas/{id}/chat/send',                 [ClassroomChatController::class, 'send'])->name('kelas.chat.send');
    Route::post('/api/kelas/{id}/chat/send-media',           [ClassroomChatController::class, 'sendMedia'])->name('kelas.chat.sendMedia');
    // Camera page for kelas (mobile)
    Route::get('/kelas/{id}/camera', function ($id) {
        abort_unless(\Auth::guard('bk')->check() || \Auth::guard('siswa')->check(), 403);
        $kelas = \App\Models\Classroom::findOrFail($id);
        return view('shared.sections.camera', [
            'roomId'  => $id,
            'backUrl' => route('kelas.chat.room', $kelas->slug),
        ]);
    })->name('kelas.camera');
    Route::post('/api/kelas/{id}/chat/mark-read',            [ClassroomChatController::class, 'markRead'])->name('kelas.chat.markRead');

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
