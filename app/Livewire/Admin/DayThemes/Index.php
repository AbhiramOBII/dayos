<?php

namespace App\Livewire\Admin\DayThemes;

use App\Models\DayTheme;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    public function delete(int $id): void
    {
        DayTheme::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.day-themes.index', [
            'themes' => DayTheme::with('pillars')->latest()->get(),
        ]);
    }
}
