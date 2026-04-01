<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Models\Attendee;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CheckInAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'checkIn';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (Attendee $record): string => $record->isCheckedIn() ? 'Checked In' : 'Check In')
            ->icon(fn (Attendee $record): string => $record->isCheckedIn() ? 'heroicon-o-check-badge' : 'heroicon-o-qr-code')
            ->color(fn (Attendee $record): string => $record->isCheckedIn() ? 'success' : 'primary')
            ->disabled(fn (Attendee $record): bool => $record->isCheckedIn())
            ->requiresConfirmation()
            ->modalHeading('Check In Attendee')
            ->modalDescription(fn (Attendee $record): string => "Check in {$record->first_name} {$record->last_name}?")
            ->action(function (Attendee $record): void {
                $record->update(['checked_in_at' => now()]);

                Notification::make()
                    ->title('Attendee checked in')
                    ->success()
                    ->send();
            });
    }
}
