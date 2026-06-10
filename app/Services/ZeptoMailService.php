<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZeptoMailService
{
    private string $apiKey;
    private string $from;
    private string $fromName;
    private string $endpoint = 'https://api.zeptomail.in/v1.1/email';

    public function __construct()
    {
        $this->apiKey   = config('services.zeptomail.key', '');
        $this->from     = config('services.zeptomail.from', '');
        $this->fromName = config('services.zeptomail.from_name', 'DayOS');

        if (empty($this->apiKey)) {
            throw new RuntimeException('ZEPTOMAIL_API_KEY is not set in your .env file.');
        }
    }

    /**
     * Send a plain-text email.
     */
    public function text(string $toAddress, string $toName, string $subject, string $body): Response
    {
        return $this->send($toAddress, $toName, $subject, nl2br(htmlspecialchars($body)));
    }

    /**
     * Send an HTML email.
     */
    public function html(string $toAddress, string $toName, string $subject, string $htmlBody): Response
    {
        return $this->send($toAddress, $toName, $subject, $htmlBody);
    }

    /**
     * Core send method.
     */
    public function send(string $toAddress, string $toName, string $subject, string $htmlBody, ?string $fromAddress = null, ?string $fromName = null): Response
    {
        $response = Http::withHeaders([
            'Authorization' => 'Zoho-enczapikey ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->post($this->endpoint, [
            'from' => [
                'address' => $fromAddress ?? $this->from,
                'name'    => $fromName    ?? $this->fromName,
            ],
            'to' => [
                [
                    'email_address' => [
                        'address' => $toAddress,
                        'name'    => $toName,
                    ],
                ],
            ],
            'subject'  => $subject,
            'htmlbody' => $htmlBody,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'ZeptoMail API error: ' . $response->status() . ' — ' . $response->body()
            );
        }

        return $response;
    }
}
