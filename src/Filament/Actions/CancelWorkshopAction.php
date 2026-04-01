<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Models\Workshop;
use BlackpigCreatif\Magistere\Notifications\WorkshopCancelledNotification;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class CancelWorkshopAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'cancelWorkshop';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Cancel')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Cancel Workshop')
            ->modalDescription('This will cancel the workshop. All confirmed bookings will be notified.')
            ->form([
                Textarea::make('cancellation_reason')
                    ->label('Cancellation Reason (optional)')
                    ->rows(3),
            ])
            ->visible(fn (Workshop $record): bool => $record->status->canTransitionTo(WorkshopStatus::Cancelled))
            ->action(function (Workshop $record): void {
                $record->update(['status' => WorkshopStatus::Cancelled]);

                $record->bookings()
                    ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Pending])
                    ->each(function (Booking $booking) use ($record): void {
                        \Illuminate\Support\Facades\Notification::route('mail', $booking->contact_email)
                            ->notify(new WorkshopCancelledNotification($record, $booking));
                    });

                Notification::make()
                    ->title('Workshop cancelled')
                    ->warning()
                    ->send();
            });
    }
}
