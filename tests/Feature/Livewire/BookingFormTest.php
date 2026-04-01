<?php

use BlackpigCreatif\Magistere\Database\Factories\CourseFactory;
use BlackpigCreatif\Magistere\Database\Factories\ExpressionOfInterestFactory;
use BlackpigCreatif\Magistere\Database\Factories\WorkshopFactory;
use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Livewire\BookingForm;
use BlackpigCreatif\Magistere\Models\Booking;
use Livewire\Livewire;

it('renders the booking form', function (): void {
    $workshop = openWorkshop();

    Livewire::test(BookingForm::class, ['workshop' => $workshop])
        ->assertStatus(200)
        ->assertSet('submitted', false)
        ->assertSet('tokenExpired', false);
});

it('starts with one blank attendee row', function (): void {
    $workshop = openWorkshop();

    Livewire::test(BookingForm::class, ['workshop' => $workshop])
        ->assertCount('attendees', 1);
});

it('can add and remove attendee rows', function (): void {
    $workshop = openWorkshop();

    Livewire::test(BookingForm::class, ['workshop' => $workshop])
        ->call('addAttendee')
        ->assertCount('attendees', 2)
        ->call('addAttendee')
        ->assertCount('attendees', 3)
        ->call('removeAttendee', 1)
        ->assertCount('attendees', 2);
});

it('cannot remove the last attendee row', function (): void {
    $workshop = openWorkshop();

    Livewire::test(BookingForm::class, ['workshop' => $workshop])
        ->call('removeAttendee', 0)
        ->assertCount('attendees', 1);
});

it('creates a booking and attendees on valid submit', function (): void {
    $workshop = openWorkshop();

    Livewire::test(BookingForm::class, ['workshop' => $workshop])
        ->set('contact.first_name', 'Jane')
        ->set('contact.last_name', 'Smith')
        ->set('contact.email', 'jane@example.com')
        ->set('contact.gdpr_consent', true)
        ->set('attendees.0.first_name', 'Jane')
        ->set('attendees.0.last_name', 'Smith')
        ->call('submit')
        ->assertSet('submitted', true);

    $booking = Booking::where('contact_email', 'jane@example.com')->first();

    expect($booking)->not->toBeNull();
    expect($booking->status)->toBe(BookingStatus::Pending);
    expect($booking->attendees()->count())->toBe(1);
    expect($booking->attendees()->where('is_primary_contact', true)->count())->toBe(1);
});

it('validates required contact fields', function (): void {
    $workshop = openWorkshop();

    Livewire::test(BookingForm::class, ['workshop' => $workshop])
        ->call('submit')
        ->assertHasErrors(['contact.first_name', 'contact.last_name', 'contact.email']);
});

it('requires gdpr consent when configured', function (): void {
    config()->set('magistere.booking.require_gdpr_consent', true);

    $workshop = openWorkshop();

    Livewire::test(BookingForm::class, ['workshop' => $workshop])
        ->set('contact.first_name', 'Jane')
        ->set('contact.last_name', 'Smith')
        ->set('contact.email', 'jane@example.com')
        ->set('contact.gdpr_consent', false)
        ->set('attendees.0.first_name', 'Jane')
        ->set('attendees.0.last_name', 'Smith')
        ->call('submit')
        ->assertHasErrors(['contact.gdpr_consent']);
});

it('shows token expired state for an expired token', function (): void {
    $workshop = openWorkshop();
    $eoi = ExpressionOfInterestFactory::new()->for($workshop)->expired()->create();

    Livewire::test(BookingForm::class, ['workshop' => $workshop, 'eoiToken' => $eoi->token])
        ->assertSet('tokenExpired', true);
});

it('pre-fills contact details from a valid eoi token', function (): void {
    $workshop = openWorkshop();
    $eoi = ExpressionOfInterestFactory::new()
        ->for($workshop)
        ->create([
            'first_name' => 'Pierre',
            'last_name' => 'Dupont',
            'email' => 'pierre@example.com',
            'token_expires_at' => now()->addHours(24),
        ]);

    Livewire::test(BookingForm::class, ['workshop' => $workshop, 'eoiToken' => $eoi->token])
        ->assertSet('contact.first_name', 'Pierre')
        ->assertSet('contact.last_name', 'Dupont')
        ->assertSet('contact.email', 'pierre@example.com');
});

it('marks eoi as converted after successful token booking', function (): void {
    $workshop = openWorkshop();
    $eoi = ExpressionOfInterestFactory::new()
        ->for($workshop)
        ->create([
            'attendee_count' => 1,
            'token_expires_at' => now()->addHours(24),
        ]);

    Livewire::test(BookingForm::class, ['workshop' => $workshop, 'eoiToken' => $eoi->token])
        ->set('contact.first_name', $eoi->first_name)
        ->set('contact.last_name', $eoi->last_name)
        ->set('contact.email', $eoi->email)
        ->set('contact.gdpr_consent', true)
        ->set('attendees.0.first_name', $eoi->first_name)
        ->set('attendees.0.last_name', $eoi->last_name)
        ->call('submit')
        ->assertSet('submitted', true);

    expect($eoi->fresh()->status)->toBe(EoiStatus::Converted);
    expect($eoi->fresh()->converted_booking_id)->not->toBeNull();
});

it('calculates the subtotal correctly based on attendee count', function (): void {
    $course = CourseFactory::new()->create(['max_capacity' => 20]);
    $workshop = WorkshopFactory::new()->for($course)->create([
        'status' => WorkshopStatus::Published,
        'price' => 150.00,
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(11),
    ]);

    $component = Livewire::test(BookingForm::class, ['workshop' => $workshop]);

    expect($component->get('subtotal'))->toBe(150.0);

    $component->call('addAttendee');

    expect($component->get('subtotal'))->toBe(300.0);
});

// ── helpers ──────────────────────────────────────────────────────────────────

function openWorkshop(): \BlackpigCreatif\Magistere\Models\Workshop
{
    return WorkshopFactory::new()
        ->for(CourseFactory::new()->state(['max_capacity' => 20]))
        ->create([
            'status' => WorkshopStatus::Published,
            'registration_opens_at' => null,
            'registration_closes_at' => null,
            'max_capacity' => null,
            'starts_at' => now()->addDays(14),
            'ends_at' => now()->addDays(15),
        ]);
}
