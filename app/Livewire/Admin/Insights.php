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
        $key = $this->aiCacheKey($from);
        cache()->forget($key);
        cache()->forget($key . '_hash');
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

        // Completed tasks this period (excluding upskilling tasks)
        $completedTasks = Task::with('pillars')
            ->where('status', TaskStatus::Completed->value)
            ->whereNull('upskilling_goal_id')
            ->whereBetween('completed_at', [$from, $to])
            ->get();

        // Completion rate vs all active non-upskilling tasks
        $totalActive    = Task::where('is_archived', false)->whereNull('upskilling_goal_id')->count() + $completedTasks->count();
        $completionRate = $totalActive > 0
            ? round(($completedTasks->count() / $totalActive) * 100)
            : 0;

        $upskillingDone = Task::where('status', TaskStatus::Completed->value)
            ->whereNotNull('upskilling_goal_id')
            ->whereBetween('completed_at', [$from, $to])
            ->count();

        // Routine KPIs
        $behaviouralRoutines = Routine::where('type', 'behavioural')->where('is_active', true)->get();
        $days        = $from->diffInDays($to) + 1;
        $totalSlots  = $behaviouralRoutines->count() * $days;
        $routineDone = RoutineLog::whereBetween('date', [$fromStr, $toStr])
            ->where('is_completed', true)->count();
        $routineRate = $totalSlots > 0 ? round(($routineDone / $totalSlots) * 100) : 0;

        // Weight-based completion metrics (the real signal)
        $completedWeight  = $completedTasks->sum(fn($t) => $t->value_points->value);
        $totalPoints      = $completedWeight; // alias kept for view
        $allTasksWeight   = Task::where('is_archived', false)->whereNull('upskilling_goal_id')->get()
                                ->sum(fn($t) => $t->value_points->value);
        // Include completed-this-period weight in the pool (they're still in DB)
        $totalPoolWeight  = $allTasksWeight > 0 ? $allTasksWeight : $completedWeight;
        $weightRate       = $totalPoolWeight > 0
                                ? round(($completedWeight / $totalPoolWeight) * 100)
                                : 0;

        // Pillar heatmap
        $pillars = Pillar::orderBy('name')->get();
        $dates   = $this->buildDateRange($from, $to);
        $heatmap = $this->buildHeatmap($completedTasks, $pillars, $dates);

        // Day-of-week pattern
        $dayPattern = $this->buildDayPattern($from, $to, $fromStr, $toStr);
        $maxPattern = max(array_column($dayPattern, 'total') ?: [1]);

        // Day tracker averages
        $trackerAvg = $this->buildTrackerAverages($fromStr, $toStr);

        // Reflections for the period
        $reflectionLogs = RoutineLog::with('routine')
            ->whereBetween('date', [$fromStr, $toStr])
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->whereHas('routine', fn($q) => $q->where('type', 'reflective'))
            ->orderBy('date')
            ->get();

        // AI summary — cache key is stable; auto-bust when reflections change by comparing stored hash
        $cacheKey     = $this->aiCacheKey($from);
        $hashKey      = $cacheKey . '_hash';
        $currentHash  = md5($reflectionLogs->pluck('content')->implode('|'));
        $storedHash   = cache()->get($hashKey);

        if ($storedHash !== $currentHash) {
            cache()->forget($cacheKey);
        }

        $aiSummary = cache()->remember(
            $cacheKey,
            now()->addHours(6),
            fn() => $this->generateAiSummary($completedTasks, $routineDone, $totalSlots, $upskillingDone, $totalPoints, $reflectionLogs, $completedWeight, $totalPoolWeight, $weightRate)
        );

        cache()->put($hashKey, $currentHash, now()->addHours(6));

        return view('livewire.admin.insights', compact(
            'from', 'to', 'completedTasks', 'completionRate', 'totalPoints',
            'upskillingDone', 'routineDone', 'routineRate', 'totalSlots',
            'pillars', 'dates', 'heatmap', 'dayPattern', 'maxPattern',
            'trackerAvg', 'aiSummary', 'reflectionLogs',
            'completedWeight', 'totalPoolWeight', 'weightRate'
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
                ->whereNull('upskilling_goal_id')
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

    private function generateAiSummary(Collection $completedTasks, int $routineDone, int $totalSlots, int $upskillingDone, int $totalPoints, Collection $reflectionLogs, int $completedWeight = 0, int $totalPoolWeight = 0, int $weightRate = 0): ?string
    {
        try {
            $periodLabel = $this->period === 'month' ? 'this month' : 'this week';

            // Top completed tasks by weight — what actually got done
            $topDone = $completedTasks->sortByDesc(fn($t) => $t->value_points->value)
                ->take(3)
                ->map(fn($t) => "'{$t->title}' ({$t->value_points->value} pts)")
                ->values()->implode(', ');

            $pillarBreakdown = $completedTasks->flatMap->pillars
                ->groupBy('name')->map->count()
                ->sortByDesc(fn($c) => $c)
                ->map(fn($c, $n) => "$n: $c")->values()->implode(', ');

            // Build reflections block
            $reflectionsBlock = '';
            if ($reflectionLogs->isNotEmpty()) {
                $lines = $reflectionLogs->map(function ($log) {
                    $date  = \Carbon\Carbon::parse($log->date)->format('D d M');
                    $title = $log->routine?->title ?? 'Reflection';
                    return "  [{$date}] {$title}: \"{$log->content}\"";
                })->implode("\n");

                $reflectionsBlock = "\n\nUSER'S JOURNAL ENTRIES ({$periodLabel}):\n{$lines}";
            }

            $prompt = "Here is what the user accomplished {$periodLabel}:

WHAT THEY COMPLETED:
- Tasks finished: {$completedTasks->count()} · Weight delivered: {$completedWeight} pts out of {$totalPoolWeight} pts total ({$weightRate}%)"
. ($topDone ? "\n- Highest-value tasks done: {$topDone}" : '')
. ($upskillingDone ? "\n- Upskilling tasks completed: {$upskillingDone}" : '')
. ($pillarBreakdown ? "\n- Life areas worked on: {$pillarBreakdown}" : '')
. "\n- Daily habits kept: {$routineDone}/{$totalSlots}"
. $reflectionsBlock . "

Using ONLY what was accomplished and the journal entries, write a warm 3–4 sentence reflection. Open by genuinely acknowledging what was done — especially the weight of the completed tasks, not just the count. If they did heavy work, say so with warmth. Draw on the journal entries to reflect their inner experience back with care. Do NOT mention what is undone, pending, or incomplete — that is not your role here. Never use words like 'only', 'just', 'still', 'yet', 'despite', 'however', or 'but'. Close with a gentle, hopeful observation — not a directive. Pure flowing prose, no bullets, no headers, under 110 words.";

            $system = 'You are a warm, deeply empathetic therapist with access to someone\'s personal productivity journal. Your entire purpose is to make this person feel genuinely seen, valued, and understood — never evaluated or pushed. You celebrate what was done, especially the heavy meaningful work. You never mention what was not done, never frame anything as a gap, never suggest they should do more. You speak with the softness of someone who truly cares. If the numbers seem low to an outsider, you trust the person\'s inner experience and reflect the emotional truth in their journal instead. Pure prose only — no bullets, no headers, no bold text.';

            return app(AnthropicService::class)->message($prompt, $system, 400);
        } catch (\Exception) {
            return null;
        }
    }
}
