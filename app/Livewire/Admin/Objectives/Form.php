<?php

namespace App\Livewire\Admin\Objectives;

use App\Enums\MeasurementType;
use App\Models\QuarterlyObjective;
use App\Models\Routine;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Form extends Component
{
    public ?int $objectiveId = null;

    public string $title           = '';
    public string $startDate       = '';
    public string $measurementType = 'number';
    public string $target          = '';
    public string $notes           = '';
    public bool   $isActive        = true;

    // Linked tasks: [task_id => contribution_override_or_null]
    public array $linkedTasks    = [];
    public array $linkedRoutines = [];

    // Search helpers
    public string $taskSearch    = '';
    public string $routineSearch = '';

    public function mount(?int $id = null): void
    {
        $this->startDate = now()->toDateString();

        if ($id) {
            $obj = QuarterlyObjective::with(['tasks', 'routines'])
                ->where('user_id', auth()->id())
                ->findOrFail($id);

            $this->objectiveId    = $id;
            $this->title          = $obj->title;
            $this->startDate      = $obj->start_date->toDateString();
            $this->measurementType= $obj->measurement_type->value;
            $this->target         = (string) $obj->target;
            $this->notes          = $obj->notes ?? '';
            $this->isActive       = $obj->is_active;

            foreach ($obj->tasks as $t) {
                $this->linkedTasks[$t->id] = $t->pivot->contribution;
            }
            foreach ($obj->routines as $r) {
                $this->linkedRoutines[$r->id] = $r->pivot->contribution_per_completion;
            }
        }
    }

    public function toggleTask(int $id): void
    {
        if (isset($this->linkedTasks[$id])) {
            unset($this->linkedTasks[$id]);
        } else {
            $this->linkedTasks[$id] = null;
        }
    }

    public function toggleRoutine(int $id): void
    {
        if (isset($this->linkedRoutines[$id])) {
            unset($this->linkedRoutines[$id]);
        } else {
            $this->linkedRoutines[$id] = 1;
        }
    }

    public function save(): void
    {
        $this->validate([
            'title'          => 'required|string|max:255',
            'startDate'      => 'required|date',
            'measurementType'=> 'required|in:number,days,currency,percentage,boolean',
            'target'         => 'required|numeric|min:0',
        ]);

        $obj = QuarterlyObjective::updateOrCreate(
            ['id' => $this->objectiveId],
            [
                'user_id'          => auth()->id(),
                'title'            => $this->title,
                'start_date'       => $this->startDate,
                'measurement_type' => $this->measurementType,
                'target'           => $this->target,
                'notes'            => $this->notes ?: null,
                'is_active'        => $this->isActive,
            ]
        );

        // Sync tasks
        $taskSync = [];
        foreach ($this->linkedTasks as $taskId => $contribution) {
            $taskSync[$taskId] = ['contribution' => $contribution ?: null];
        }
        $obj->tasks()->sync($taskSync);

        // Sync routines
        $routineSync = [];
        foreach ($this->linkedRoutines as $routineId => $contribution) {
            $routineSync[$routineId] = ['contribution_per_completion' => $contribution ?: 1];
        }
        $obj->routines()->sync($routineSync);

        $this->redirect(route('admin.objectives.index'), navigate: true);
    }

    public function render()
    {
        $tasks = Task::where('is_archived', false)
            ->whereNull('upskilling_goal_id')
            ->when($this->taskSearch, fn($q) => $q->where('title', 'like', '%' . $this->taskSearch . '%'))
            ->orderBy('title')
            ->get();

        $routines = Routine::where('is_active', true)
            ->when($this->routineSearch, fn($q) => $q->where('title', 'like', '%' . $this->routineSearch . '%'))
            ->orderBy('title')
            ->get();

        return view('livewire.admin.objectives.form', [
            'tasks'            => $tasks,
            'routines'         => $routines,
            'measurementTypes' => MeasurementType::cases(),
        ]);
    }
}
