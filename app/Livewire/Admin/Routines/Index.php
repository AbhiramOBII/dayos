<?php

namespace App\Livewire\Admin\Routines;

use App\Enums\RoutineType;
use App\Models\Routine;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    public string $typeFilter = '';
    public bool $showInactive = false;

    public function toggleActive(int $id): void
    {
        $routine = Routine::findOrFail($id);
        $routine->update(['is_active' => ! $routine->is_active]);
    }

    public function delete(int $id): void
    {
        Routine::findOrFail($id)->delete();
    }

    public function render()
    {
        $routines = Routine::query()
            ->when($this->typeFilter !== '', fn ($q) => $q->where('type', $this->typeFilter))
            ->when(! $this->showInactive, fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('livewire.admin.routines.index', [
            'routines' => $routines,
            'types'    => RoutineType::cases(),
        ]);
    }
}
