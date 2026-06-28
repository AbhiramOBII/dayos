<?php

namespace App\Livewire\Admin;

use App\Enums\TaskStatus;
use App\Services\AnthropicService;
use App\Models\DailyThemeAssignment;
use App\Models\ObjectiveLog;
use App\Models\QuarterlyObjective;
use App\Models\TaskTbcbLog;
use App\Models\UpskillingGoal;
use App\Models\DayTheme;
use App\Models\Routine;
use App\Models\RoutineLog;
use App\Models\Pillar;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Today extends Component
{
    public array  $reflections = [];
    public bool   $showAddTask = false;

    // Objective log
    public ?int   $logObjectiveId = null;
    public string $logValue       = '';
    public string $logNote        = '';
    public bool   $logSuccess     = false;
    public string $newTaskTitle = '';
    public string $newTaskDescription = '';
    public int    $newTaskPoints = 5;
    public array  $newTaskPillars = [];

    public bool $showTodayThemePicker    = false;
    public bool $showTomorrowThemePicker = false;
    public ?int $selectedTodayThemeId    = null;
    public ?int $selectedTomorrowThemeId = null;

    public function mount(): void
    {
        $today = now()->toDateString();

        $logs = RoutineLog::where('date', $today)->get()->keyBy('routine_id');

        foreach (Routine::where('type', 'reflective')->where('is_active', true)->get() as $r) {
            $this->reflections[$r->id] = $logs->get($r->id)?->content ?? '';
        }

        $todayAssignment    = DailyThemeAssignment::where('date', $today)->first();
        $tomorrowAssignment = DailyThemeAssignment::where('date', now()->addDay()->toDateString())->first();

        $this->selectedTodayThemeId    = $todayAssignment?->day_theme_id;
        $this->selectedTomorrowThemeId = $tomorrowAssignment?->day_theme_id;
    }

    public function toggleBehavioural(int $routineId): void
    {
        $today = now()->toDateString();
        $log   = RoutineLog::firstOrCreate(
            ['routine_id' => $routineId, 'date' => $today],
            ['is_completed' => false]
        );
        $log->update(['is_completed' => ! $log->is_completed]);
    }

    public function saveReflection(int $routineId): void
    {
        RoutineLog::updateOrCreate(
            ['routine_id' => $routineId, 'date' => now()->toDateString()],
            ['content' => $this->reflections[$routineId] ?? '', 'is_completed' => true]
        );
    }

    public function saveTodayTheme(): void
    {
        if (! $this->selectedTodayThemeId) {
            return;
        }
        DailyThemeAssignment::updateOrCreate(
            ['date' => now()->toDateString()],
            ['day_theme_id' => $this->selectedTodayThemeId]
        );
        $this->showTodayThemePicker = false;
    }

    public function saveTomorrowTheme(): void
    {
        if (! $this->selectedTomorrowThemeId) {
            return;
        }
        DailyThemeAssignment::updateOrCreate(
            ['date' => now()->addDay()->toDateString()],
            ['day_theme_id' => $this->selectedTomorrowThemeId]
        );
        $this->showTomorrowThemePicker = false;
    }

    public function quickAddTask(): void
    {
        $this->validate(['newTaskTitle' => 'required|string|max:255']);

        $task = Task::create([
            'title'             => $this->newTaskTitle,
            'short_description' => $this->newTaskDescription ?: null,
            'value_points'      => $this->newTaskPoints,
            'status'            => TaskStatus::WIP->value,
            'is_archived'       => false,
        ]);

        if (! empty($this->newTaskPillars)) {
            $task->pillars()->sync($this->newTaskPillars);
        }

        $this->reset('newTaskTitle', 'newTaskDescription', 'newTaskPillars', 'showAddTask');
        $this->newTaskPoints = 5;
    }

    public function setTbcbDate(int $taskId, ?string $date): void
    {
        $task    = Task::findOrFail($taskId);
        $oldDate = $task->tbcb_date?->format('Y-m-d');
        $newDate = $date ?: null;

        if ($oldDate !== $newDate) {
            TaskTbcbLog::create([
                'task_id'  => $task->id,
                'old_date' => $oldDate,
                'new_date' => $newDate,
            ]);
            $task->update(['tbcb_date' => $newDate]);
        }
    }

    public function toggleTaskStatus(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $task->update([
            'status' => $task->status === TaskStatus::WIP
                ? TaskStatus::Backlog->value
                : TaskStatus::WIP->value,
        ]);
    }

    public function completeTask(int $taskId): void
    {
        Task::findOrFail($taskId)->update(['status' => TaskStatus::Completed->value]);
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
            'logged_date'  => now()->toDateString(),
        ]);

        $this->reset(['logObjectiveId', 'logValue', 'logNote']);
        $this->logSuccess = true;
    }

    public function resetLogSuccess(): void
    {
        $this->logSuccess = false;
    }

    public function render()
    {
        $today    = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $isEvening = now()->hour >= 21;

        $todayAssignment = DailyThemeAssignment::with('dayTheme')->where('date', $today)->first();
        $theme           = $todayAssignment?->dayTheme;

        $hasTomorrowTheme = DailyThemeAssignment::where('date', $tomorrow)->exists();

        $behaviouralRoutines = Routine::where('type', 'behavioural')->where('is_active', true)->orderBy('sort_order')->get();
        $reflectiveRoutines  = Routine::where('type', 'reflective')->where('is_active', true)->orderBy('sort_order')->get();
        $todayLogs           = RoutineLog::where('date', $today)->get()->keyBy('routine_id');

        $cutoff = now()->addDays(2)->toDateString();
        $tasks = Task::with(['tbcbLogs', 'upskillingGoal'])->where('is_archived', false)
            ->where(function ($q) use ($cutoff) {
                $q->where('status', TaskStatus::WIP->value)
                  ->orWhere(function ($inner) use ($cutoff) {
                      $inner->where('status', TaskStatus::Backlog->value)
                            ->where(function ($d) use ($cutoff) {
                                $d->whereNull('tbcb_date')
                                  ->orWhere('tbcb_date', '<=', $cutoff);
                            });
                  });
            })
            ->orderByDesc('value_points')
            ->orderByRaw('ISNULL(tbcb_date), tbcb_date ASC')
            ->get();

        $allThemes = DayTheme::orderBy('title')->get();
        $pillars   = Pillar::orderBy('name')->get();

        $behaviouralDone  = $todayLogs->filter(fn ($l) => $l->is_completed)->count();
        $behaviouralTotal = $behaviouralRoutines->count();

        $dailyQuote = cache()->remember('daily_quote_' . $today, now()->copy()->addDay()->startOfDay(), function () use ($theme) {
            try {
                $themeContext = $theme
                    ? "Today's theme is \"{$theme->title}\""
                      . ($theme->description ? " — {$theme->description}" : '') . '. '
                    : '';

                $system = 'You are a personal motivational coach. Respond with ONLY the quote. No preamble, no explanation, no quotation marks around the full response. If it is a known quote, append — Author Name at the end.';
                $prompt = "{$themeContext}Give me one powerful, concise motivational quote for today. Keep it under 25 words.";

                return app(AnthropicService::class)->message($prompt, $system, 120);
            } catch (\Exception) {
                return null;
            }
        });

        $activeUpskillingGoal = null;
        $upskillingTodayCount = 0;
        $upskillingTotalCount = 0;
        $upskillingDoneCount  = 0;

        $upskillingIds = $tasks->whereNotNull('upskilling_goal_id')->pluck('upskilling_goal_id')->unique();
        if ($upskillingIds->isNotEmpty()) {
            $activeUpskillingGoal = UpskillingGoal::where('status', 'active')
                ->whereIn('id', $upskillingIds)->first();
            if ($activeUpskillingGoal) {
                $upskillingTodayCount = $tasks->where('upskilling_goal_id', $activeUpskillingGoal->id)->count();
                $upskillingTotalCount = $activeUpskillingGoal->tasks()->count();
                $upskillingDoneCount  = $activeUpskillingGoal->tasks()->where('status', TaskStatus::Completed->value)->count();
            }
        }

        $activeObjectives = QuarterlyObjective::where('user_id', auth()->id())
            ->where('start_date', '<=', $today)
            ->whereRaw('DATE_ADD(start_date, INTERVAL 30 DAY) >= ?', [$today])
            ->orderBy('title')
            ->get();

        $objTodayLogs = ObjectiveLog::with('objective')
            ->whereIn('objective_id', $activeObjectives->pluck('id'))
            ->where('logged_date', $today)
            ->latest()
            ->get();

        return view('livewire.admin.today', compact(
            'theme', 'today', 'tomorrow', 'isEvening', 'hasTomorrowTheme',
            'behaviouralRoutines', 'reflectiveRoutines', 'todayLogs',
            'tasks', 'allThemes', 'behaviouralDone', 'behaviouralTotal',
            'dailyQuote', 'activeUpskillingGoal', 'upskillingTodayCount',
            'upskillingTotalCount', 'upskillingDoneCount', 'pillars',
            'activeObjectives', 'objTodayLogs'
        ));
    }
}
