<?php

namespace App\Console\Commands;

use App\Services\AnthropicService;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMorningMotivation extends Command
{
    protected $signature   = 'push:morning-motivation';
    protected $description = 'Send a motivational morning push notification at 7:00 AM IST';

    public function handle(PushNotificationService $push, AnthropicService $claude): void
    {
        $today = now()->toDateString();

        $body = cache()->remember('morning_motivation_' . $today, now()->endOfDay(), function () use ($claude) {
            try {
                $system = 'You are an energetic personal coach. Respond with ONLY the motivational message — no preamble, no quotes around it. Keep it under 20 words. Be positive and action-oriented.';
                $prompt = 'Give me one powerful, energising morning message to start the day with focus and intent.';
                return $claude->message($prompt, $system, 80);
            } catch (\Exception $e) {
                Log::warning('Morning motivation AI failed: ' . $e->getMessage());
                return 'Good morning! Today is your chance to move the needle. Let\'s make it count.';
            }
        });

        $push->sendToAll(
            title: '☀️ Good Morning',
            body:  $body,
            data:  ['type' => 'morning_motivation'],
        );

        $this->info('Morning motivation sent.');
    }
}
