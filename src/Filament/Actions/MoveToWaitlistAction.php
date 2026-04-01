<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Notifications\BookingWaitlistedNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class MoveToWaitlistAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'moveToWaitlist';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Move to Waitlist')
            ->icon('heroicon-o-clock')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Move to Waitlist')
            ->modalDescription('This will move the booking to the waitlist and notify the contact.')
            ->visible(fn (Booking $record): bool => $record->status->canTransitionTo(BookingStatus::Waitlisted))
            ->action(function (Booking $record): void {
                $record->update(['status' => BookingStatus::Waitlisted]);

                \Illuminate\Support\Facades\Notification::route('mail', $record->contact_email)
                    ->notify(new BookingWaitlistedNotification($record));

                Notification::make()
                    ->title('Booking moved to waitlist')
                    ->warning()
                    ->send();
            });
    }
}
