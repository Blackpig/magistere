<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Notifications\BookingConfirmedNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ConfirmBookingAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'confirmBooking';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Confirm Booking')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Confirm Booking')
            ->modalDescription('This will confirm the booking and notify the contact by email.')
            ->visible(fn (Booking $record): bool => $record->status->canTransitionTo(BookingStatus::Confirmed))
            ->action(function (Booking $record): void {
                $record->update([
                    'status' => BookingStatus::Confirmed,
                    'confirmed_at' => now(),
                ]);

                \Illuminate\Support\Facades\Notification::route('mail', $record->contact_email)
                    ->notify(new BookingConfirmedNotification($record));

                Notification::make()
                    ->title('Booking confirmed')
                    ->success()
                    ->send();
            });
    }
}
