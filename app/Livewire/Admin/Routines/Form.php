<?php

namespace App\Livewire\Admin\Routines;

use App\Enums\RoutineType;
use App\Models\Routine;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Form extends Component
{
    public ?Routine $routine = null;

    public string $title       = '';
    public string $description = '';
    public string $type        = 'behavioural';
    public string $prompt      = '';
    public bool   $is_active   = true;
    public int    $sort_order  = 0;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->routine       = Routine::findOrFail($id);
            $this->title         = $this->routine->title;
            $this->description   = $this->routine->description ?? '';
            $this->type          = $this->routine->type->value;
            $this->prompt        = $this->routine->prompt ?? '';
            $this->is_active     = $this->routine->is_active;
            $this->sort_order    = $this->routine->sort_order;
        }
    }

    public function save(): void
    {
        $this->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'type'             => 'required|string|in:behavioural,reflective',
            'prompt'           => 'nullable|string',
            'is_active'        => 'boolean',
            'sort_order'       => 'integer|min:0',
        ]);

        $data = [
            'title'       => $this->title,
            'description' => $this->description ?: null,
            'type'        => $this->type,
            'prompt'      => $this->type === 'reflective' ? ($this->prompt ?: null) : null,
            'is_active'   => $this->is_active,
            'sort_order'  => $this->sort_order,
        ];

        if ($this->routine) {
            $this->routine->update($data);
        } else {
            Routine::create($data);
        }

        session()->flash('success', $this->routine ? 'Routine updated.' : 'Routine created.');
        $this->redirect(route('admin.routines.index'));
    }

    public function render()
    {
        return view('livewire.admin.routines.form', [
            'routineTypes' => RoutineType::cases(),
        ]);
    }
}
