<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Models\Workshop;
use BlackpigCreatif\Magistere\Notifications\WorkshopConfirmedNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ConfirmWorkshopAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'confirmWorkshop';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Confirm')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Confirm Workshop')
            ->modalDescription('This will confirm the workshop and notify all confirmed bookings.')
            ->visible(fn (Workshop $record): bool => $record->status->canTransitionTo(WorkshopStatus::Confirmed))
            ->action(function (Workshop $record): void {
                $record->update(['status' => WorkshopStatus::Confirmed]);

                $record->bookings()
                    ->where('status', BookingStatus::Confirmed)
                    ->each(function (Booking $booking) use ($record): void {
                        $notifiable = (object) ['email' => $booking->contact_email];
                        $notifiable->email = $booking->contact_email;

                        \Illuminate\Support\Facades\Notification::route('mail', $booking->contact_email)
                            ->notify(new WorkshopConfirmedNotification($record, $booking));
                    });

                Notification::make()
                    ->title('Workshop confirmed')
                    ->success()
                    ->send();
            });
    }
}
