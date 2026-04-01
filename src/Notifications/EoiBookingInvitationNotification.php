<?php

namespace BlackpigCreatif\Magistere\Notifications;

use BlackpigCreatif\Magistere\Models\ExpressionOfInterest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EoiBookingInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ExpressionOfInterest $eoi,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('A place is available — complete your booking')
            ->line('Great news! A place has become available on the workshop you expressed interest in.')
            ->line('Use the link below to complete your booking before it expires.')
            ->action('Complete Booking', url('/'))
            ->line('This link expires at ' . $this->eoi->token_expires_at?->format('d M Y H:i') . '.');
    }
}
