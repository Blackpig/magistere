<?php

namespace BlackpigCreatif\Magistere\Notifications;

use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkshopCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Workshop $workshop,
        public readonly Booking $booking,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Workshop Cancelled')
            ->line('Unfortunately the workshop has been cancelled.')
            ->line('Workshop: ' . $this->workshop->slug)
            ->line('Booking reference: ' . $this->booking->reference)
            ->line('A member of our team will be in touch regarding a refund.');
    }
}
