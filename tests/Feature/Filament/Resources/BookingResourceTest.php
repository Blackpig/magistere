<?php

use BlackpigCreatif\Magistere\Database\Factories\BookingFactory;
use BlackpigCreatif\Magistere\Database\Factories\CourseFactory;
use BlackpigCreatif\Magistere\Database\Factories\WorkshopFactory;
use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Filament\Resources\BookingResource\Pages\CreateBooking;
use BlackpigCreatif\Magistere\Filament\Resources\BookingResource\Pages\EditBooking;
use BlackpigCreatif\Magistere\Filament\Resources\BookingResource\Pages\ListBookings;
use BlackpigCreatif\Magistere\Models\Booking;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;
use Workbench\App\Models\User;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('can list bookings', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();
    $bookings = BookingFactory::new()->for($workshop)->count(3)->create();

    Livewire::test(ListBookings::class)
        ->assertCanSeeTableRecords($bookings);
});

it('can create a booking', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();

    Livewire::test(CreateBooking::class)
        ->fillForm([
            'workshop_id' => $workshop->id,
            'contact_first_name' => 'Alice',
            'contact_last_name' => 'Dupont',
            'contact_email' => 'alice@example.com',
            'status' => BookingStatus::Pending,
            'attendee_count' => 1,
            'subtotal' => 100,
            'currency' => 'EUR',
            'gdpr_consent' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Booking::where('contact_email', 'alice@example.com')->exists())->toBeTrue();
});

it('can filter bookings by status', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();
    BookingFactory::new()->for($workshop)->pending()->create();
    BookingFactory::new()->for($workshop)->confirmed()->create();

    Livewire::test(ListBookings::class)
        ->filterTable('status', BookingStatus::Pending->value)
        ->assertCountTableRecords(1);
});

it('can soft delete a booking', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();
    $booking = BookingFactory::new()->for($workshop)->create();

    Livewire::test(EditBooking::class, ['record' => $booking->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect($booking->fresh()->trashed())->toBeTrue();
});

it('can search bookings by contact name', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();
    BookingFactory::new()->for($workshop)->create([
        'contact_first_name' => 'Unique',
        'contact_last_name' => 'Person',
    ]);
    BookingFactory::new()->for($workshop)->create([
        'contact_first_name' => 'Other',
        'contact_last_name' => 'User',
    ]);

    Livewire::test(ListBookings::class)
        ->searchTable('Unique')
        ->assertCountTableRecords(1);
});
