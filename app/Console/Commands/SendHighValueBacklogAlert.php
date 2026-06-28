<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\FcmToken;
use App\Models\Task;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class SendHighValueBacklogAlert extends Command
{
    protected $signature   = 'push:backlog-alert';
    protected $description = 'Send a push notification when high-value tasks (>34 pts) are sitting in backlog';

    public function handle(PushNotificationService $push): void
    {
        if (FcmToken::count() === 0) {
            $this->warn('No FCM tokens. Skipping.');
            return;
        }

        $highValueBacklog = Task::where('is_archived', false)
            ->where('status', TaskStatus::Backlog->value)
            ->get()
            ->filter(fn ($t) => $t->value_points->value > 34);

        if ($highValueBacklog->isEmpty()) {
            $this->info('No high-value backlog tasks. Skipping.');
            return;
        }

        $count     = $highValueBacklog->count();
        $topTask   = $highValueBacklog->sortByDesc(fn ($t) => $t->value_points->value)->first();
        $topPoints = $topTask->value_points->value;

        $body = $count === 1
            ? "'{$topTask->title}' ({$topPoints} pts) is still in backlog. Time to move it forward!"
            : "{$count} high-value tasks (>{$topPoints}+ pts) are stuck in backlog. Don't let them wait!";

        $push->sendToAll(
            title: '🔥 High-Value Tasks Need Attention',
            body:  $body,
            data:  ['type' => 'backlog_alert', 'url' => '/admin/tasks'],
        );

        $this->info("Backlog alert sent for {$count} task(s).");
    }
}
