<?php

namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private string $projectId;
    private string $serviceAccountPath;

    public function __construct()
    {
        $this->projectId          = config('services.firebase.project_id');
        $this->serviceAccountPath = config('services.firebase.service_account');
    }

    /**
     * Send a push notification to all stored FCM tokens.
     */
    public function sendToAll(string $title, string $body, array $data = [], ?string $icon = null): void
    {
        $tokens = FcmToken::pluck('token');

        foreach ($tokens as $token) {
            $this->sendToToken($token, $title, $body, $data, $icon);
        }
    }

    /**
     * Send to a specific FCM token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], ?string $icon = null): bool
    {
        try {
            $accessToken = $this->getAccessToken();

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'webpush' => [
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                            'icon'  => $icon ?? '/images/app-icon.png',
                            'badge' => '/images/app-icon.png',
                            'vibrate' => [200, 100, 200],
                        ],
                        'fcm_options' => [
                            'link' => config('app.url') . '/admin/today',
                        ],
                    ],
                    'data' => array_map('strval', $data),
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $payload);

            if ($response->failed()) {
                Log::warning('FCM send failed', ['token' => substr($token, 0, 20), 'response' => $response->body()]);

                // Remove invalid tokens
                if ($response->status() === 404 || str_contains($response->body(), 'UNREGISTERED')) {
                    FcmToken::where('token', $token)->delete();
                }

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FCM exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a short-lived OAuth2 access token from the service account JSON.
     */
    private function getAccessToken(): string
    {
        if (! file_exists($this->serviceAccountPath)) {
            throw new \RuntimeException('Firebase service account JSON not found at: ' . $this->serviceAccountPath);
        }

        $sa = json_decode(file_get_contents($this->serviceAccountPath), true);

        $now = time();
        $header  = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64url_encode(json_encode([
            'iss'   => $sa['client_email'],
            'sub'   => $sa['client_email'],
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ]));

        $signingInput = "{$header}.{$payload}";
        openssl_sign($signingInput, $signature, $sa['private_key'], OPENSSL_ALGO_SHA256);
        $jwt = "{$signingInput}." . base64url_encode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        return $response->json('access_token');
    }
}

if (! function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
