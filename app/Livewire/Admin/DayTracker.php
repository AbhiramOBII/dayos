<?php

namespace App\Livewire\Admin;

use App\Models\DailyTimeline;
use App\Models\ObjectiveLog;
use App\Models\QuarterlyObjective;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class DayTracker extends Component
{
    public string $today;

    // Objective log form
    public ?int   $logObjectiveId = null;
    public string $logValue       = '';
    public string $logNote        = '';
    public bool   $logSuccess     = false;

    public function mount(): void
    {
        $this->today = now()->toDateString();
    }

    public function logProgress(): void
    {
        $this->validate([
            'logObjectiveId' => 'required|exists:quarterly_objectives,id',
            'logValue'       => 'required|numeric|min:0.01',
        ]);

        ObjectiveLog::create([
            'objective_id' => $this->logObjectiveId,
            'value'        => $this->logValue,
            'note'         => $this->logNote ?: null,
            'logged_date'  => $this->today,
        ]);

        $this->reset(['logValue', 'logNote']);
        $this->logSuccess = true;
    }

    public function resetLogSuccess(): void
    {
        $this->logSuccess = false;
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

        $today = $this->today;
        $activeObjectives = QuarterlyObjective::where('user_id', auth()->id())
            ->where('start_date', '<=', $today)
            ->whereRaw('DATE_ADD(start_date, INTERVAL 30 DAY) >= ?', [$today])
            ->orderBy('title')
            ->get();

        $todayLogs = ObjectiveLog::with('objective')
            ->whereIn('objective_id', $activeObjectives->pluck('id'))
            ->where('logged_date', $today)
            ->latest()
            ->get();

        return view('livewire.admin.day-tracker', compact('todayRecord', 'history', 'activeObjectives', 'todayLogs'));
    }
}
