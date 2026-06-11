<?php

namespace App\Livewire\Admin\PeopleMet;

use App\Models\PersonMet;
use App\Services\AnthropicService;
use App\Services\ZeptoMailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class Form extends Component
{
    use WithFileUploads;

    public ?int $personId = null;

    public string $mode = 'manual';

    public string $met_at       = '';
    public string $name         = '';
    public string $email        = '';
    public string $phone        = '';
    public string $company      = '';
    public string $place        = '';
    public string $location     = '';
    public string $context      = '';

    public $cardImageFile  = null;
    public ?string $existingCardImage = null;

    public bool   $scanning    = false;
    public ?string $scanError  = null;

    public function mount(int $id = null): void
    {
        $this->met_at = now()->format('Y-m-d\TH:i');

        if ($id) {
            $person = PersonMet::findOrFail($id);
            $this->personId          = $person->id;
            $this->met_at            = $person->met_at->format('Y-m-d\TH:i');
            $this->name              = $person->name ?? '';
            $this->email             = $person->email ?? '';
            $this->phone             = $person->phone ?? '';
            $this->company           = $person->company ?? '';
            $this->place             = $person->place ?? '';
            $this->location          = $person->location ?? '';
            $this->context           = $person->context ?? '';
            $this->existingCardImage = $person->card_image_url;
        }
    }

    public function scanCard(): void
    {
        $this->validate(['cardImageFile' => 'required|image|max:5120']);

        $this->scanning  = true;
        $this->scanError = null;

        try {
            $mime   = $this->cardImageFile->getMimeType();
            $base64 = base64_encode(file_get_contents($this->cardImageFile->getRealPath()));

            $data = app(AnthropicService::class)->extractBusinessCard($base64, $mime);

            if (empty($data)) {
                $this->scanError = 'AI could not extract any data from this image. Please check the log or try a clearer photo.';
            } else {
                if (!empty($data['name']))     $this->name     = $data['name'];
                if (!empty($data['email']))    $this->email    = $data['email'];
                if (!empty($data['phone']))    $this->phone    = $data['phone'];
                if (!empty($data['company']))  $this->company  = $data['company'];
                if (!empty($data['location'])) $this->location = $data['location'];

                $this->mode = 'manual';
            }
        } catch (\Exception $e) {
            $this->scanError = 'Could not extract data: ' . $e->getMessage();
        } finally {
            $this->scanning = false;
        }
    }

    public function save(): void
    {
        $this->validate([
            'met_at'        => 'required|date',
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|max:255',
            'phone'         => 'nullable|string|max:50',
            'company'       => 'nullable|string|max:255',
            'place'         => 'nullable|string|max:255',
            'location'      => 'nullable|string|max:255',
            'context'       => 'nullable|string',
            'cardImageFile' => 'nullable|image|max:5120',
        ]);

        $cardPath = null;
        if ($this->cardImageFile) {
            $ext      = $this->cardImageFile->getClientOriginalExtension();
            $filename = uniqid() . '.' . $ext;
            $cardPath = $this->cardImageFile->storePubliclyAs('people-met/cards', $filename, 'spaces');
        }

        $data = [
            'met_at'   => $this->met_at,
            'name'     => $this->name,
            'email'    => $this->email ?: null,
            'phone'    => $this->phone ?: null,
            'company'  => $this->company ?: null,
            'place'    => $this->place ?: null,
            'location' => $this->location ?: null,
            'context'  => $this->context ?: null,
        ];

        if ($cardPath) {
            $data['card_image'] = $cardPath;
        }

        if ($this->personId) {
            PersonMet::findOrFail($this->personId)->update($data);
        } else {
            PersonMet::create($data);

            if (!empty($this->email)) {
                $this->sendIntroEmail();
            }
        }

        $this->redirect(route('admin.people-met.index'), navigate: true);
    }

    private function sendIntroEmail(): void
    {
        try {
            $html = view('emails.introduction', [
                'name'      => $this->name,
                'eventName' => $this->place ?: 'our recent meeting',
            ])->render();

            $pdfPath     = public_path('obiikriationzwebllp-profile.pdf');
            $attachments = file_exists($pdfPath) ? [$pdfPath] : [];

            app(ZeptoMailService::class)->send(
                $this->email,
                $this->name,
                'Connecting after ' . ($this->place ?: 'our recent meeting') . ' — Abhiram Chandramohan',
                $html,
                $attachments
            );
        } catch (\Exception $e) {
            Log::error('PeopleMet auto intro email failed', [
                'to_email'   => $this->email,
                'to_name'    => $this->name,
                'event_name' => $this->place,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.people-met.form');
    }
}
