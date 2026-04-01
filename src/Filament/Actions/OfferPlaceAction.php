<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Models\ExpressionOfInterest;
use BlackpigCreatif\Magistere\Notifications\EoiBookingInvitationNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class OfferPlaceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'offerPlace';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Offer Place')
            ->icon('heroicon-o-gift')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Offer a Place')
            ->modalDescription('This will send a booking invitation link to this person. The link will expire based on the configured token expiry hours.')
            ->visible(fn (ExpressionOfInterest $record): bool => in_array($record->status, [EoiStatus::New, EoiStatus::Contacted], true))
            ->action(function (ExpressionOfInterest $record): void {
                $record->refreshToken();
                $record->update([
                    'status' => EoiStatus::Contacted,
                    'notified_at' => now(),
                ]);

                \Illuminate\Support\Facades\Notification::route('mail', $record->email)
                    ->notify(new EoiBookingInvitationNotification($record));

                Notification::make()
                    ->title('Booking invitation sent')
                    ->success()
                    ->send();
            });
    }
}
