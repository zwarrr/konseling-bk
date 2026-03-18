<?php

namespace App\Services;

use App\Models\Users\PushSubscription;
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
    }
}
