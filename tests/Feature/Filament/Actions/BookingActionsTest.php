<?php

use BlackpigCreatif\Magistere\Database\Factories\BookingFactory;
use BlackpigCreatif\Magistere\Database\Factories\CourseFactory;
use BlackpigCreatif\Magistere\Database\Factories\WorkshopFactory;
use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Filament\Actions\ConfirmBookingAction;
use BlackpigCreatif\Magistere\Filament\Actions\MoveToWaitlistAction;
use BlackpigCreatif\Magistere\Filament\Resources\BookingResource\Pages\EditBooking;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs(\Workbench\App\Models\User::factory()->create());
});

it('can confirm a pending booking', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->pending()
        ->create();

    Livewire::test(EditBooking::class, ['record' => $booking->getRouteKey()])
        ->callAction(ConfirmBookingAction::class);

    expect($booking->fresh()->status)->toBe(BookingStatus::Confirmed);
    expect($booking->fresh()->confirmed_at)->not->toBeNull();
});

it('confirm action is hidden for already confirmed bookings', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->confirmed()
        ->create();

    Livewire::test(EditBooking::class, ['record' => $booking->getRouteKey()])
        ->assertActionHidden(ConfirmBookingAction::class);
});

it('can move a pending booking to the waitlist', function (): void {
    $booking = BookingFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->pending()
        ->create();

    Livewire::test(EditBooking::class, ['record' => $booking->getRouteKey()])
        ->callAction(MoveToWaitlistAction::class);

    expect($booking->fresh()->status)->toBe(BookingStatus::Waitlisted);
});
