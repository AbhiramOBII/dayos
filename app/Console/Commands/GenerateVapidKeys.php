<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature   = 'webpush:vapid';
    protected $description = 'Generate VAPID public/private keys for Web Push and print them for .env';

    public function handle(): void
    {
        $keys = \Minishlink\WebPush\VAPID::createVapidKeys();

        $this->info('Add these to your .env file:');
        $this->line('');
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->line('');
        $this->comment('VAPID_SUBJECT should be mailto:you@yourdomain.com');
    }
}
