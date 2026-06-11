<?php

namespace App\Livewire\Admin\PeopleMet;

use App\Models\PersonMet;
use App\Services\ZeptoMailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    public string $search = '';

    public bool    $showEmailModal = false;
    public ?int    $emailPersonId  = null;
    public string  $eventName      = '';
    public bool    $emailSending   = false;
    public ?string $emailSuccess   = null;
    public ?string $emailError     = null;

    public function openEmailModal(int $id): void
    {
        $this->emailPersonId = $id;
        $this->eventName     = '';
        $this->emailSuccess  = null;
        $this->emailError    = null;
        $this->showEmailModal = true;
    }

    public function closeEmailModal(): void
    {
        $this->showEmailModal = false;
        $this->emailPersonId  = null;
        $this->eventName      = '';
    }

    public function sendIntroEmail(): void
    {
        $this->validate([
            'eventName' => 'required|string|max:255',
        ], [
            'eventName.required' => 'Please enter the event or place where you met.',
        ]);

        $person = PersonMet::findOrFail($this->emailPersonId);

        if (empty($person->email)) {
            $this->emailError = 'This contact has no email address saved.';
            return;
        }

        $this->emailSending = true;
        $this->emailError   = null;

        try {
            $html = view('emails.introduction', [
                'name'      => $person->name,
                'eventName' => $this->eventName,
            ])->render();

            $pdfPath = public_path('obiikriationzwebllp-profile.pdf');
            $attachments = file_exists($pdfPath) ? [$pdfPath] : [];

            app(ZeptoMailService::class)->send(
                $person->email,
                $person->name,
                'Connecting after ' . $this->eventName . ' — Abhiram Chandramohan',
                $html,
                $attachments
            );

            $this->emailSuccess  = 'Introduction email sent to ' . $person->email . '!';
            $this->showEmailModal = false;
        } catch (\Exception $e) {
            $this->emailError = 'Failed to send: ' . $e->getMessage();
            Log::error('PeopleMet intro email failed', [
                'person_id'  => $this->emailPersonId,
                'to_email'   => $person->email ?? null,
                'event_name' => $this->eventName,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
        } finally {
            $this->emailSending = false;
        }
    }

    public function delete(int $id): void
    {
        $person = PersonMet::findOrFail($id);

        if ($person->card_image) {
            Storage::disk('spaces')->delete($person->card_image);
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
