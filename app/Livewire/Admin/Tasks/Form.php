<?php

namespace App\Livewire\Admin\Tasks;

use App\Enums\TaskPoints;
use App\Enums\TaskStatus;
use App\Models\Pillar;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Form extends Component
{
    public ?Task $task = null;

    public string $title = '';
    public string $short_description = '';
    public int $value_points = 3;
    public string $status = 'backlog';
    public string $tbcb_date = '';
    public bool $is_archived = false;
    public array $selectedPillars = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->task = Task::with('pillars')->findOrFail($id);
            $this->title = $this->task->title;
            $this->short_description = $this->task->short_description ?? '';
            $this->value_points = $this->task->value_points->value;
            $this->status = $this->task->status->value;
            $this->tbcb_date = $this->task->tbcb_date?->format('Y-m-d') ?? '';
            $this->is_archived = $this->task->is_archived;
            $this->selectedPillars = $this->task->pillars->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'value_points' => 'required|integer|in:3,5,8,13,21,34,55',
            'status' => 'required|string|in:backlog,wip,completed',
            'tbcb_date' => 'nullable|date',
            'is_archived' => 'boolean',
            'selectedPillars' => 'nullable|array',
            'selectedPillars.*' => 'exists:pillars,id',
        ]);

        $data = [
            'title' => $this->title,
            'short_description' => $this->short_description,
            'value_points' => $this->value_points,
            'status' => $this->status,
            'tbcb_date' => $this->tbcb_date ?: null,
            'is_archived' => $this->is_archived,
        ];

        if ($this->task) {
            $this->task->update($data);
            $this->task->pillars()->sync($this->selectedPillars);
        } else {
            $task = Task::create($data);
            $task->pillars()->sync($this->selectedPillars);
        }

        session()->flash('success', $this->task ? 'Task updated.' : 'Task created.');
        $this->redirect(route('admin.tasks.index'));
    }

    public function render()
    {
        return view('livewire.admin.tasks.form', [
            'availablePoints' => TaskPoints::cases(),
            'availableStatuses' => TaskStatus::cases(),
            'availablePillars' => Pillar::orderBy('name')->get(),
        ]);
    }
}
