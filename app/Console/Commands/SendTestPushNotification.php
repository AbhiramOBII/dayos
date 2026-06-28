<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendTestPushNotification extends Command
{
    protected $signature   = 'push:test {--title=DayOS} {--body=Test notification from DayOS!}';
    protected $description = 'Send a test push notification to all subscribed devices';

    public function handle(): void
    {
        $tokens = \App\Models\PushSubscription::count();
        if ($tokens === 0) {
            $this->warn('No push subscriptions registered. Open the app and click the bell icon first.');
            return;
        }

        $this->info("Sending to {$tokens} device(s)…");

        app(\App\Services\PushNotificationService::class)->sendToAll(
            $this->option('title'),
            $this->option('body'),
        );

        $this->info('Done.');
    }
}
