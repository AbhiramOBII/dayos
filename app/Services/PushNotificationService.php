<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject'    => config('services.vapid.subject'),
                'publicKey'  => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ];

        $this->webPush = new WebPush($auth);
        $this->webPush->setReuseVAPIDHeaders(true);
        $this->webPush->setDefaultOptions(['TTL' => 3600]);
    }

    /**
     * Send a push notification to all stored subscriptions.
     */
    public function sendToAll(string $title, string $body, array $data = [], ?string $icon = null): void
    {
        $subscriptions = PushSubscription::all();

        if ($subscriptions->isEmpty()) {
            Log::info('WebPush: no subscriptions registered.');
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'icon'  => $icon ?? '/images/app-icon.png',
            'badge' => '/images/app-icon.png',
            'url'   => $data['url'] ?? '/admin/today',
            'data'  => $data,
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'publicKey'       => $sub->public_key,
                'authToken'       => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
            ]);

            $this->webPush->queueNotification($subscription, $payload);
        }

        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if (! $report->isSuccess()) {
                Log::warning('WebPush send failed', [
                    'endpoint' => substr($endpoint, 0, 60),
                    'reason'   => $report->getReason(),
                ]);

                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                    Log::info('WebPush: removed expired subscription.');
                }
            }
        }
    }
}

if (! function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
