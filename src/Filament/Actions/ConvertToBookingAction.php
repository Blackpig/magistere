<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Models\ExpressionOfInterest;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ConvertToBookingAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'convertToBooking';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Convert to Booking')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Convert to Booking')
            ->modalDescription('This will create a pending booking pre-filled from this expression of interest and mark the EOI as converted.')
            ->visible(fn (ExpressionOfInterest $record): bool => in_array($record->status, [EoiStatus::New, EoiStatus::Contacted], true))
            ->action(function (ExpressionOfInterest $record): void {
                $booking = Booking::create([
                    'workshop_id' => $record->workshop_id,
                    'contact_first_name' => $record->first_name,
                    'contact_last_name' => $record->last_name,
                    'contact_email' => $record->email,
                    'contact_phone' => $record->phone,
                    'attendee_count' => $record->attendee_count,
                    'status' => BookingStatus::Pending,
                    'subtotal' => 0,
                    'currency' => config('magistere.currency', 'EUR'),
                    'gdpr_consent' => false,
                ]);

                $record->update(['status' => EoiStatus::Converted]);

                Notification::make()
                    ->title('Booking created')
                    ->body('Reference: ' . $booking->reference)
                    ->success()
                    ->send();
            });
    }
}
