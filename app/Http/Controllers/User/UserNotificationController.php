<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProgramBooking;
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
        $utype = $user->role === 'guru' ? 'bk' : 'siswa';

        // Types that must NEVER appear for each role
        $excludeTypes = $utype === 'siswa'
            ? ['kelas_join_request', 'kelas_approved', 'kelas_rejected']
            : [];

        $notifs = UserNotification::where('user_id', $user->id)
            ->where('user_type', $utype)
            ->when($excludeTypes, fn($q) => $q->whereNotIn('type', $excludeTypes))
            ->orderByDesc('created_at')
            ->paginate(20);

        $bookingIds = $notifs->getCollection()
            ->pluck('related_type')
            ->filter(fn($t) => is_string($t) && str_starts_with($t, 'program_booking:'))
            ->map(fn($t) => (int) substr($t, strlen('program_booking:')))
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $bookingMeta = $bookingIds->isEmpty()
            ? collect()
            : ProgramBooking::query()
                ->whereIn('id', $bookingIds)
                ->get(['id', 'method', 'booking_type'])
                ->keyBy('id');

        // Mark all as read when page is opened
        UserNotification::where('user_id', $user->id)
            ->where('user_type', $utype)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $role = $user->role ?? 'siswa';
        $pfx  = $role === 'guru' ? 'bk' : 'siswa';

        return view('users.sections.notifikasi', compact('notifs', 'pfx', 'role', 'bookingMeta'));
    }

    /**
     * JSON count of unread notifications.
     */
    public function count(): JsonResponse
    {
        $userType = auth()->user()?->role === 'guru' ? 'bk' : 'siswa';
        $excludeTypes = $userType === 'siswa'
            ? ['kelas_join_request', 'kelas_approved', 'kelas_rejected']
            : [];

        $unread = UserNotification::where('user_id', auth()->id())
            ->where('user_type', $userType)
            ->when($excludeTypes, fn($q) => $q->whereNotIn('type', $excludeTypes))
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread' => $unread]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(int $id): JsonResponse
    {
        $notif = UserNotification::where('user_id', auth()->id())
            ->where('user_type', auth()->user()?->role === 'guru' ? 'bk' : 'siswa')
            ->findOrFail($id);
        $notif->markAsRead();

        return response()->json(['ok' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        UserNotification::where('user_id', auth()->id())
            ->where('user_type', auth()->user()?->role === 'guru' ? 'bk' : 'siswa')
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

        $utype = auth()->user()?->role === 'guru' ? 'bk' : 'siswa';

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $hash],
            [
                'user_id'  => auth()->id(),
                'user_type' => $utype,
                'endpoint' => $endpoint,
                'p256dh'   => $validated['keys']['p256dh'],
                'auth'     => $validated['keys']['auth'],
            ]
        );

        return response()->json(['ok' => true]);
    }
}
