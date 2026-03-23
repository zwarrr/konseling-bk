<?php

namespace App\Services;

use App\Mail\GenericSystemNotificationMail;
use App\Models\BkAccount;
use App\Models\SiswaAccount;
use App\Models\Users\PushSubscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;

class PushNotificationService
{
    protected WebPush $webPush;

    public function __construct()
    {
        $vapidPublic  = config('vapid.public_key');
        $vapidPrivate = config('vapid.private_key');

        if (!$vapidPublic || !$vapidPrivate) {
            // VAPID not configured — push notifications disabled
            $this->webPush = new WebPush();
            return;
        }

        $this->webPush = new WebPush([
            'VAPID' => [
                'subject'    => config('app.url', 'mailto:admin@example.com'),
                'publicKey'  => $vapidPublic,
                'privateKey' => $vapidPrivate,
            ],
        ]);
    }

    /**
     * Send a push notification to all active subscriptions.
     */
    public function broadcastToAll(string $title, string $body = '', array $data = []): void
    {
        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'data'  => $data,
        ]);

        $subscriptions = PushSubscription::all();
        $staleIds = [];

        foreach ($subscriptions as $sub) {
            try {
                $subscription = Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'publicKey'       => $sub->p256dh,
                    'authToken'       => $sub->auth,
                    'contentEncoding' => 'aesgcm',
                ]);
                $this->webPush->queueNotification($subscription, $payload);
            } catch (\Throwable) {
                // Skip invalid subscriptions
            }
        }

        // Flush and clean up expired subscriptions
        foreach ($this->webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                // Remove expired subscription
                PushSubscription::where('endpoint', $report->getRequest()->getUri()->__toString())->delete();
            }
        }
    }

    /**
     * Send a push notification to a specific user.
     */
    public function sendToUser(int $userId, string $title, string $body = '', array $data = [], ?string $userType = null): void
    {
        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'data'  => $data,
        ]);

        $subs = PushSubscription::query()
            ->where('user_id', $userId)
            ->when($userType, fn($q) => $q->where('user_type', $userType))
            ->get();

        foreach ($subs as $sub) {
            try {
                $subscription = Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'publicKey'       => $sub->p256dh,
                    'authToken'       => $sub->auth,
                    'contentEncoding' => 'aesgcm',
                ]);
                $this->webPush->queueNotification($subscription, $payload);
            } catch (\Throwable) {
            }
        }

        foreach ($this->webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                PushSubscription::where('endpoint', $report->getRequest()->getUri()->__toString())->delete();
            }
        }

        $this->sendEmailNotification($userId, $userType, $title, $body);
    }

    protected function sendEmailNotification(int $userId, ?string $userType, string $title, string $body): void
    {
        if (!config('mail.notifications_enabled')) return;

        $email = $this->resolveUserEmail($userId, $userType);
        if (!$email) return;

        try {
            Mail::to($email)->send(new GenericSystemNotificationMail($title, $body));
        } catch (\Throwable $e) {
            // Keep push flow stable if SMTP fails.
            Log::warning('Gagal mengirim notifikasi email.', [
                'user_id' => $userId,
                'user_type' => $userType,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function resolveUserEmail(int $userId, ?string $userType): ?string
    {
        $userType = strtolower((string) $userType);

        if ($userType === 'bk') {
            return BkAccount::whereKey($userId)->value('email');
        }

        if ($userType === 'siswa' || $userType === 'user') {
            return SiswaAccount::whereKey($userId)->value('email');
        }

        // Fallback: try both roles when type is not provided.
        return BkAccount::whereKey($userId)->value('email')
            ?? SiswaAccount::whereKey($userId)->value('email');
    }
}
