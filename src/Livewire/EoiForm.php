<?php

namespace BlackpigCreatif\Magistere\Livewire;

use BlackpigCreatif\Magistere\Enums\EoiSource;
use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Models\ExpressionOfInterest;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\View\View;
use Livewire\Component;

class EoiForm extends Component
{
    public Workshop $workshop;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public int $attendeeCount = 1;

    public string $message = '';

    /** Stored as the enum's string value so Livewire can wire it without type conflicts. */
    public string $source = 'interest';

    public bool $submitted = false;

    public function mount(Workshop $workshop, string $source = 'interest'): void
    {
        $this->workshop = $workshop;
        $this->source = $source;
    }

    /** @return array<string, array<int, string>> */
    protected function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'attendeeCount' => ['required', 'integer', 'min:1'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        ExpressionOfInterest::create([
            'workshop_id' => $this->workshop->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'attendee_count' => $this->attendeeCount,
            'message' => $this->message ?: null,
            'source' => EoiSource::from($this->source),
            'status' => EoiStatus::New,
        ]);

        $this->submitted = true;
    }

    public function render(): View
    {
        return view('magistere::livewire.eoi-form');
    }
}
