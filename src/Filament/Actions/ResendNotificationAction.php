<?php

namespace BlackpigCreatif\Magistere\Filament\Actions;

use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Models\ExpressionOfInterest;
use BlackpigCreatif\Magistere\Notifications\EoiBookingInvitationNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ResendNotificationAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'resendNotification';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Resend Invitation')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Resend Booking Invitation')
            ->modalDescription('This will refresh the token and resend the booking invitation link.')
            ->visible(fn (ExpressionOfInterest $record): bool => $record->status === EoiStatus::Contacted)
            ->action(function (ExpressionOfInterest $record): void {
                $record->refreshToken();
                $record->update(['notified_at' => now()]);

                \Illuminate\Support\Facades\Notification::route('mail', $record->email)
                    ->notify(new EoiBookingInvitationNotification($record));

                Notification::make()
                    ->title('Invitation resent')
                    ->success()
                    ->send();
            });
    }
}
