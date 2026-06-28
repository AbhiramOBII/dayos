<?php

namespace App\Livewire\Admin\Objectives;

use App\Models\QuarterlyObjective;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    public string $filter = 'active'; // active | upcoming | past | all

    public function delete(int $id): void
    {
        QuarterlyObjective::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    public function render()
    {
        $today = now()->toDateString();

        $objectives = QuarterlyObjective::with(['tasks', 'routines'])
            ->where('user_id', auth()->id())
            ->when($this->filter === 'active',   fn($q) => $q->where('start_date', '<=', $today)
                                                              ->whereRaw('DATE_ADD(start_date, INTERVAL 30 DAY) >= ?', [$today]))
            ->when($this->filter === 'upcoming', fn($q) => $q->where('start_date', '>', $today))
            ->when($this->filter === 'past',     fn($q) => $q->whereRaw('DATE_ADD(start_date, INTERVAL 30 DAY) < ?', [$today]))
            ->orderByDesc('start_date')
            ->get();

        return view('livewire.admin.objectives.index', compact('objectives'));
    }
}
