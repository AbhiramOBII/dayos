<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
     *
     * @param  array<string>  $attachmentPaths  Absolute file paths to attach
     */
    public function send(string $toAddress, string $toName, string $subject, string $htmlBody, array $attachmentPaths = [], ?string $fromAddress = null, ?string $fromName = null): Response
    {
        $payload = [
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
        ];

        if (!empty($attachmentPaths)) {
            $payload['attachments'] = array_map(function (string $path) {
                return [
                    'name'      => basename($path),
                    'content'   => base64_encode(file_get_contents($path)),
                    'mime_type' => mime_content_type($path) ?: 'application/octet-stream',
                ];
            }, $attachmentPaths);
        }

        Log::info('ZeptoMail sending email', [
            'to'      => $toAddress,
            'subject' => $subject,
            'from'    => $payload['from']['address'] ?? null,
            'has_attachments' => !empty($payload['attachments']),
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-enczapikey ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->post($this->endpoint, $payload);

        if ($response->failed()) {
            Log::error('ZeptoMail API error', [
                'to'          => $toAddress,
                'subject'     => $subject,
                'status'      => $response->status(),
                'body'        => $response->body(),
            ]);
            throw new RuntimeException(
                'ZeptoMail API error: ' . $response->status() . ' — ' . $response->body()
            );
        }

        Log::info('ZeptoMail email sent successfully', [
            'to'     => $toAddress,
            'status' => $response->status(),
        ]);

        return $response;
    }
}
