<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Users\PushSubscription;
use App\Models\Users\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    /**
     * Notification history page.
     */
    public function index(): View
    {
        $user  = auth()->user();
        $notifs = UserNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        // Mark all as read when page is opened
        UserNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $role = $user->role ?? 'siswa';
        $pfx  = $role === 'guru' ? 'bk' : 'siswa';

        return view('users.sections.notifikasi', compact('notifs', 'pfx', 'role'));
    }

    /**
     * JSON count of unread notifications.
     */
    public function count(): JsonResponse
    {
        $unread = UserNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread' => $unread]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(int $id): JsonResponse
    {
        $notif = UserNotification::where('user_id', auth()->id())->findOrFail($id);
        $notif->markAsRead();

        return response()->json(['ok' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        UserNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /**
     * Save or update a Web Push subscription.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth'   => 'required|string',
        ]);

        $endpoint = $validated['endpoint'];
        $hash     = md5($endpoint);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $hash],
            [
                'user_id'  => auth()->id(),
                'endpoint' => $endpoint,
                'p256dh'   => $validated['keys']['p256dh'],
                'auth'     => $validated['keys']['auth'],
            ]
        );

        return response()->json(['ok' => true]);
    }
}
