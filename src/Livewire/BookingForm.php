<?php

namespace BlackpigCreatif\Magistere\Livewire;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Enums\ExtraPer;
use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Models\ExpressionOfInterest;
use BlackpigCreatif\Magistere\Models\Extra;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BookingForm extends Component
{
    public Workshop $workshop;

    public ?ExpressionOfInterest $eoi = null;

    /** @var array<string, mixed> */
    public array $contact = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'organisation' => '',
        'gdpr_consent' => false,
        'marketing_consent' => false,
        'notes' => '',
    ];

    /**
     * Each row: ['first_name', 'last_name', 'email', 'dietary_requirements', 'accessibility_requirements']
     *
     * @var array<int, array<string, string>>
     */
    public array $attendees = [];

    /**
     * Extra selections: [extra_id => quantity]
     * For ExtraPer::Attendee, quantity is treated as a boolean (0 or 1).
     *
     * @var array<int, int>
     */
    public array $selectedExtras = [];

    public bool $submitted = false;

    public ?Booking $booking = null;

    public bool $tokenExpired = false;

    public function mount(Workshop $workshop, ?string $eoiToken = null): void
    {
        $this->workshop = $workshop->load(['course', 'location', 'extras']);

        if ($eoiToken !== null) {
            $eoi = ExpressionOfInterest::where('token', $eoiToken)
                ->where('workshop_id', $workshop->id)
                ->first();

            if (! $eoi || $eoi->isTokenExpired()) {
                $this->tokenExpired = true;

                return;
            }

            $this->eoi = $eoi;

            $this->contact['first_name'] = $eoi->first_name;
            $this->contact['last_name'] = $eoi->last_name;
            $this->contact['email'] = $eoi->email;
            $this->contact['phone'] = $eoi->phone ?? '';

            // Pre-fill attendee rows from EOI count
            for ($i = 0; $i < $eoi->attendee_count; $i++) {
                $this->attendees[] = $this->blankAttendee();
            }
        }

        if (empty($this->attendees)) {
            $this->attendees[] = $this->blankAttendee();
        }

        // Pre-select required extras
        foreach ($this->workshop->extras as $extra) {
            if ($extra->is_required) {
                $this->selectedExtras[$extra->id] = 1;
            }
        }
    }

    public function addAttendee(): void
    {
        $this->attendees[] = $this->blankAttendee();
    }

    public function removeAttendee(int $index): void
    {
        if (count($this->attendees) <= 1) {
            return;
        }

        array_splice($this->attendees, $index, 1);
        $this->attendees = array_values($this->attendees);
    }

    public function getSubtotalProperty(): float
    {
        $attendeeCount = count($this->attendees);
        $basePrice = (float) ($this->workshop->price ?? $this->workshop->course?->price ?? 0);
        $total = $basePrice * $attendeeCount;

        foreach ($this->workshop->extras as $extra) {
            $quantity = (int) ($this->selectedExtras[$extra->id] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $total += $extra->per === ExtraPer::Attendee
                ? (float) $extra->price * $attendeeCount * $quantity
                : (float) $extra->price * $quantity;
        }

        return round($total, 2);
    }

    /** @return array<string, array<int, string>> */
    protected function rules(): array
    {
        $requireGdpr = config('magistere.booking.require_gdpr_consent', true);
        $collectMarketing = config('magistere.booking.collect_marketing_consent', true);

        $rules = [
            'contact.first_name' => ['required', 'string', 'max:255'],
            'contact.last_name' => ['required', 'string', 'max:255'],
            'contact.email' => ['required', 'email', 'max:255'],
            'contact.phone' => ['nullable', 'string', 'max:50'],
            'contact.organisation' => ['nullable', 'string', 'max:255'],
            'contact.notes' => ['nullable', 'string', 'max:2000'],
            'contact.gdpr_consent' => $requireGdpr ? ['accepted'] : ['boolean'],
            'contact.marketing_consent' => $collectMarketing ? ['boolean'] : [],
            'attendees' => ['required', 'array', 'min:1'],
            'attendees.*.first_name' => ['required', 'string', 'max:255'],
            'attendees.*.last_name' => ['required', 'string', 'max:255'],
            'attendees.*.email' => ['nullable', 'email', 'max:255'],
            'attendees.*.dietary_requirements' => ['nullable', 'string', 'max:1000'],
            'attendees.*.accessibility_requirements' => ['nullable', 'string', 'max:1000'],
        ];

        foreach ($this->workshop->extras as $extra) {
            if ($extra->is_required) {
                continue;
            }

            $rules["selectedExtras.{$extra->id}"] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    public function submit(): void
    {
        $this->validate();

        $booking = DB::transaction(function (): Booking {
            $attendeeCount = count($this->attendees);

            /** @var Booking $booking */
            $booking = Booking::create([
                'workshop_id' => $this->workshop->id,
                'contact_first_name' => $this->contact['first_name'],
                'contact_last_name' => $this->contact['last_name'],
                'contact_email' => $this->contact['email'],
                'contact_phone' => $this->contact['phone'] ?: null,
                'contact_organisation' => $this->contact['organisation'] ?: null,
                'status' => BookingStatus::Pending,
                'attendee_count' => $attendeeCount,
                'subtotal' => $this->subtotal,
                'currency' => $this->workshop->currency ?? config('magistere.currency', 'EUR'),
                'notes' => $this->contact['notes'] ?: null,
                'gdpr_consent' => (bool) $this->contact['gdpr_consent'],
                'marketing_consent' => (bool) ($this->contact['marketing_consent'] ?? false),
            ]);

            // Create attendee records
            foreach ($this->attendees as $i => $row) {
                $booking->attendees()->create([
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'] ?: null,
                    'dietary_requirements' => $row['dietary_requirements'] ?: null,
                    'accessibility_requirements' => $row['accessibility_requirements'] ?: null,
                    'is_primary_contact' => $i === 0,
                ]);
            }

            // Attach selected extras
            $extrasSync = [];
            foreach ($this->selectedExtras as $extraId => $quantity) {
                if ($quantity > 0) {
                    $extrasSync[$extraId] = ['quantity' => (int) $quantity];
                }
            }

            if (! empty($extrasSync)) {
                $booking->extras()->sync($extrasSync);
            }

            // Mark EOI as converted if token flow
            if ($this->eoi !== null) {
                $this->eoi->update([
                    'status' => EoiStatus::Converted,
                    'converted_booking_id' => $booking->id,
                ]);
            }

            return $booking;
        });

        $this->booking = $booking;
        $this->submitted = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('magistere::livewire.booking-form', [
            'extras' => $this->workshop->extras,
        ]);
    }

    /** @return array<string, string> */
    private function blankAttendee(): array
    {
        return [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'dietary_requirements' => '',
            'accessibility_requirements' => '',
        ];
    }
}
