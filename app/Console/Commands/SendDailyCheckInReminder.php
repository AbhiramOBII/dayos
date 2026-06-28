<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\FcmToken;
use App\Models\Routine;
use App\Models\RoutineLog;
use App\Models\Task;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class SendDailyCheckInReminder extends Command
{
    protected $signature   = 'push:checkin-reminder';
    protected $description = 'Send a 5 PM reminder if the user has NOT updated routines, reflections, or tasks today';

    public function handle(PushNotificationService $push): void
    {
        if (FcmToken::count() === 0) {
            $this->warn('No FCM tokens. Skipping.');
            return;
        }

        $today = now()->toDateString();
        $messages = [];

        // 1. Check behavioural routines
        $behaviouralTotal = Routine::where('type', 'behavioural')->where('is_active', true)->count();
        $behaviouralDone  = RoutineLog::where('date', $today)
            ->where('is_completed', true)
            ->whereHas('routine', fn ($q) => $q->where('type', 'behavioural'))
            ->count();

        if ($behaviouralTotal > 0 && $behaviouralDone === 0) {
            $messages[] = 'daily habits';
        }

        // 2. Check reflective routines
        $reflectiveTotal = Routine::where('type', 'reflective')->where('is_active', true)->count();
        $reflectiveDone  = RoutineLog::where('date', $today)
            ->where('is_completed', true)
            ->whereHas('routine', fn ($q) => $q->where('type', 'reflective'))
            ->count();

        if ($reflectiveTotal > 0 && $reflectiveDone === 0) {
            $messages[] = 'reflections';
        }

        // 3. Check WIP tasks — any task moved to WIP or completed counts as "updated"
        $tasksUpdatedToday = Task::where('is_archived', false)
            ->where(function ($q) {
                $q->where('status', TaskStatus::Completed->value)
                  ->orWhere('status', TaskStatus::WIP->value);
            })
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        if ($tasksUpdatedToday === 0) {
            $messages[] = 'tasks';
        }

        if (empty($messages)) {
            $this->info('User is up to date — no reminder needed.');
            return;
        }

        $missing = implode(', ', $messages);
        $body    = "Hey! You haven't logged your {$missing} today. Take 2 minutes to check in. 💪";

        $push->sendToAll(
            title: '⏰ Daily Check-In',
            body:  $body,
            data:  ['type' => 'checkin_reminder', 'url' => '/admin/today'],
        );

        $this->info("Check-in reminder sent. Missing: {$missing}");
    }
}
