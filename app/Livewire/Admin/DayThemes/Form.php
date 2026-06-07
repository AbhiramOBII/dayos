<?php

namespace App\Livewire\Admin\DayThemes;

use App\Models\DayTheme;
use App\Models\Pillar;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Form extends Component
{
    public ?DayTheme $theme = null;

    public string $title = '';
    public string $short_label = '';
    public string $description = '';
    public array $selectedPillars = [];
    public string $ideal_day = '';
    public string $color = '#151828';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->theme = DayTheme::with('pillars')->findOrFail($id);
            $this->title = $this->theme->title;
            $this->short_label = $this->theme->short_label;
            $this->description = $this->theme->description ?? '';
            $this->selectedPillars = $this->theme->pillars->pluck('id')->map(fn ($id) => (string) $id)->toArray();
            $this->ideal_day = $this->theme->ideal_day ?? '';
            $this->color = $this->theme->color ?? '#151828';
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'short_label' => 'required|string|max:50',
            'description' => 'nullable|string',
            'selectedPillars' => 'required|array|min:1',
            'selectedPillars.*' => 'exists:pillars,id',
            'ideal_day' => 'nullable|string',
            'color' => 'required|string|max:7',
        ]);

        $data = [
            'title' => $this->title,
            'short_label' => $this->short_label,
            'description' => $this->description,
            'ideal_day' => $this->ideal_day,
            'color' => $this->color,
        ];

        if ($this->theme) {
            $this->theme->update($data);
            $this->theme->pillars()->sync($this->selectedPillars);
        } else {
            $theme = DayTheme::create($data);
            $theme->pillars()->sync($this->selectedPillars);
        }

        session()->flash('success', $this->theme ? 'Theme updated.' : 'Theme created.');
        $this->redirect(route('admin.day-themes.index'));
    }

    public function render()
    {
        return view('livewire.admin.day-themes.form', [
            'availablePillars' => Pillar::orderBy('name')->get(),
        ]);
    }
}
