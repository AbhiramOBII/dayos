<?php

namespace App\Livewire\Admin;

use App\Models\DailyTimeline;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class DayTracker extends Component
{
    public string $today;

    public function mount(): void
    {
        $this->today = now()->toDateString();
    }

    public function saveField(string $field, ?string $value): void
    {
        $allowed = ['wake_up_time', 'office_time', 'lunch_time', 'come_home_time', 'dinner_time', 'sleep_time'];

        if (! in_array($field, $allowed)) {
            return;
        }

        DailyTimeline::updateOrCreate(
            ['date' => $this->today],
            [$field => $value ?: null]
        );
    }

    public function saveHistoryField(string $date, string $field, ?string $value): void
    {
        $allowed = ['wake_up_time', 'office_time', 'lunch_time', 'come_home_time', 'dinner_time', 'sleep_time'];

        if (! in_array($field, $allowed) || $date >= $this->today) {
            return;
        }

        DailyTimeline::updateOrCreate(
            ['date' => $date],
            [$field => $value ?: null]
        );
    }

    public function render()
    {
        $todayRecord = DailyTimeline::where('date', $this->today)->first();

        $history = DailyTimeline::where('date', '<', $this->today)
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        return view('livewire.admin.day-tracker', compact('todayRecord', 'history'));
    }
}
