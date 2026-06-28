<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $html = view('emails.introduction', [
        'name'      => 'Abhiram',
        'eventName' => 'TiE Bengaluru Summit 2025',
    ])->render();

    $pdfPath = public_path('obiikriationzwebllp-profile.pdf');
    $attachments = file_exists($pdfPath) ? [$pdfPath] : [];

    echo (empty($attachments) ? "WARNING: PDF not found.\n\n" : "PDF found — attaching.\n\n");

    $response = app(\App\Services\ZeptoMailService::class)->send(
        'abhiram.chandramohan@gmail.com',
        'Abhiram C',
        'Connecting after TiE Bengaluru Summit 2025 — Abhiram Chandramohan',
        $html,
        $attachments
    );

    echo "SUCCESS — HTTP " . $response->status() . "\n";
} catch (\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
