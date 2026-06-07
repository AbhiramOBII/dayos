<?php

namespace App\Livewire\Admin\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    #[Url]
    public string $statusFilter = '';

    #[Url]
    public bool $showArchived = false;

    public function delete(int $id): void
    {
        Task::findOrFail($id)->delete();
    }

    public function toggleArchive(int $id): void
    {
        $task = Task::findOrFail($id);
        $task->update(['is_archived' => ! $task->is_archived]);
    }

    public function render()
    {
        $query = Task::with('pillars')
            ->when(! $this->showArchived, fn ($q) => $q->where('is_archived', false))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest();

        return view('livewire.admin.tasks.index', [
            'tasks' => $query->get(),
            'statuses' => TaskStatus::cases(),
        ]);
    }
}
