<?php

namespace App\Livewire\Admin;

use App\Enums\TaskStatus;
use App\Models\DailyTimeline;
use App\Models\Pillar;
use App\Models\Routine;
use App\Models\RoutineLog;
use App\Models\Task;
use App\Services\AnthropicService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Insights extends Component
{
    public string $period = 'week';

    public function setPeriod(string $period): void
    {
        $this->period = in_array($period, ['week', 'month']) ? $period : 'week';
    }

    public function regenerateSummary(): void
    {
        [$from] = $this->getRange();
        cache()->forget($this->aiCacheKey($from));
    }

    private function getRange(): array
    {
        return match ($this->period) {
            'month' => [now()->startOfMonth(), now()->copy()->endOfMonth()],
            default => [now()->startOfWeek(Carbon::MONDAY), now()->copy()->endOfWeek(Carbon::SUNDAY)],
        };
    }

    private function aiCacheKey(Carbon $from): string
    {
        return $this->period === 'month'
            ? "insights_ai_month_{$from->format('Y_m')}"
            : "insights_ai_week_{$from->format('Y_W')}";
    }

    public function render()
    {
        [$from, $to] = $this->getRange();
        $fromStr = $from->toDateString();
        $toStr   = $to->toDateString();

        // Completed tasks this period
        $completedTasks = Task::with('pillars')
            ->where('status', TaskStatus::Completed->value)
            ->whereBetween('completed_at', [$from, $to])
            ->get();

        // Completion rate vs all active tasks
        $totalActive    = Task::where('is_archived', false)->count() + $completedTasks->count();
        $completionRate = $totalActive > 0
            ? round(($completedTasks->count() / $totalActive) * 100)
            : 0;

        $upskillingDone = $completedTasks->whereNotNull('upskilling_goal_id')->count();

        // Routine KPIs
        $behaviouralRoutines = Routine::where('type', 'behavioural')->where('is_active', true)->get();
        $days        = $from->diffInDays($to) + 1;
        $totalSlots  = $behaviouralRoutines->count() * $days;
        $routineDone = RoutineLog::whereBetween('date', [$fromStr, $toStr])
            ->where('is_completed', true)->count();
        $routineRate = $totalSlots > 0 ? round(($routineDone / $totalSlots) * 100) : 0;

        // Total value points
        $totalPoints = $completedTasks->sum(fn($t) => $t->value_points->value);

        // Pillar heatmap
        $pillars = Pillar::orderBy('name')->get();
        $dates   = $this->buildDateRange($from, $to);
        $heatmap = $this->buildHeatmap($completedTasks, $pillars, $dates);

        // Day-of-week pattern
        $dayPattern = $this->buildDayPattern($from, $to, $fromStr, $toStr);
        $maxPattern = max(array_column($dayPattern, 'total') ?: [1]);

        // Day tracker averages
        $trackerAvg = $this->buildTrackerAverages($fromStr, $toStr);

        // AI summary (cached)
        $aiSummary = cache()->remember(
            $this->aiCacheKey($from),
            now()->addHours(6),
            fn() => $this->generateAiSummary($completedTasks, $routineDone, $totalSlots, $upskillingDone, $totalPoints)
        );

        return view('livewire.admin.insights', compact(
            'from', 'to', 'completedTasks', 'completionRate', 'totalPoints',
            'upskillingDone', 'routineDone', 'routineRate', 'totalSlots',
            'pillars', 'dates', 'heatmap', 'dayPattern', 'maxPattern',
            'trackerAvg', 'aiSummary'
        ));
    }

    private function buildDateRange(Carbon $from, Carbon $to): array
    {
        $dates = [];
        $cur   = $from->copy();
        while ($cur <= $to) {
            $dates[] = $cur->toDateString();
            $cur->addDay();
        }
        return $dates;
    }

    private function buildHeatmap(Collection $completed, Collection $pillars, array $dates): array
    {
        $heatmap = [];
        foreach ($pillars as $pillar) {
            $heatmap[$pillar->id] = [
                'name' => $pillar->name,
                'days' => array_fill_keys($dates, 0),
            ];
        }
        foreach ($completed as $task) {
            $d = $task->completed_at?->toDateString();
            if (! $d || ! in_array($d, $dates)) {
                continue;
            }
            foreach ($task->pillars as $pillar) {
                if (isset($heatmap[$pillar->id]['days'][$d])) {
                    $heatmap[$pillar->id]['days'][$d]++;
                }
            }
        }
        return $heatmap;
    }

    private function buildDayPattern(Carbon $from, Carbon $to, string $fromStr, string $toStr): array
    {
        $labels    = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $mysqlDow  = [2, 3, 4, 5, 6, 7, 1]; // MySQL DAYOFWEEK: 1=Sun…7=Sat

        $pattern = [];
        foreach ($labels as $i => $label) {
            $dow = $mysqlDow[$i];

            $tasks = Task::where('status', TaskStatus::Completed->value)
                ->whereBetween('completed_at', [$from, $to])
                ->whereRaw('DAYOFWEEK(completed_at) = ?', [$dow])
                ->count();

            $routines = RoutineLog::whereBetween('date', [$fromStr, $toStr])
                ->where('is_completed', true)
                ->whereRaw('DAYOFWEEK(date) = ?', [$dow])
                ->count();

            $pattern[] = [
                'label'    => $label,
                'tasks'    => $tasks,
                'routines' => $routines,
                'total'    => $tasks + $routines,
            ];
        }
        return $pattern;
    }

    private function buildTrackerAverages(string $fromStr, string $toStr): array
    {
        $records = DailyTimeline::whereBetween('date', [$fromStr, $toStr])->get();
        if ($records->isEmpty()) {
            return [];
        }

        $fields = ['wake_up_time', 'office_time', 'lunch_time', 'come_home_time', 'dinner_time', 'sleep_time'];
        $labels = ['Wake Up', 'Office', 'Lunch', 'Come Home', 'Dinner', 'Sleep'];
        $avg    = [];

        foreach ($fields as $idx => $field) {
            $times = $records->pluck($field)->filter()->values();
            if ($times->isEmpty()) {
                continue;
            }
            $totalMins = $times->sum(fn($t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2));
            $avgMins   = (int) round($totalMins / $times->count());
            $avg[]     = [
                'label' => $labels[$idx],
                'time'  => sprintf('%02d:%02d', intdiv($avgMins, 60), $avgMins % 60),
                'count' => $times->count(),
            ];
        }
        return $avg;
    }

    private function generateAiSummary(Collection $completedTasks, int $routineDone, int $totalSlots, int $upskillingDone, int $totalPoints): ?string
    {
        try {
            $periodLabel  = $this->period === 'month' ? 'this month' : 'this week';
            $routineRate  = $totalSlots > 0 ? round(($routineDone / $totalSlots) * 100) : 0;
            $pillarBreakdown = $completedTasks->flatMap->pillars
                ->groupBy('name')->map->count()
                ->sortByDesc(fn($c) => $c)
                ->map(fn($c, $n) => "$n: $c")->values()->implode(', ');

            $prompt = "Productivity review for {$periodLabel}:
- Tasks completed: {$completedTasks->count()} (total value points: {$totalPoints})
- Upskilling tasks done: {$upskillingDone}
- Daily routine completion: {$routineDone}/{$totalSlots} ({$routineRate}%)"
. ($pillarBreakdown ? "\n- Work area breakdown: {$pillarBreakdown}" : '') . "

Write a warm, personal 2–3 sentence insight: what went well, what the data reveals about focus and consistency, and one concrete suggestion for the next period. Pure flowing prose, no bullet points, no headers, under 80 words.";

            $system = 'You are a personal productivity coach. Be warm, direct, insightful. Pure prose only — no bullets, no headers. Under 80 words.';

            return app(AnthropicService::class)->message($prompt, $system, 300);
        } catch (\Exception) {
            return null;
        }
    }
}
