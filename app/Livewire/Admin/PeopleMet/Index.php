<?php

namespace App\Livewire\Admin\PeopleMet;

use App\Models\PersonMet;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    public string $search = '';

    public function delete(int $id): void
    {
        $person = PersonMet::findOrFail($id);

        if ($person->card_image) {
            \Illuminate\Support\Facades\Storage::disk('spaces')->delete($person->card_image);
        }

        $person->delete();
    }

    public function render()
    {
        $people = PersonMet::query()
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('company', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->orderByDesc('met_at')
            ->paginate(20);

        return view('livewire.admin.people-met.index', compact('people'));
    }
}
