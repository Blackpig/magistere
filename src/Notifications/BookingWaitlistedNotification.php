<?php

namespace BlackpigCreatif\Magistere\Notifications;

use BlackpigCreatif\Magistere\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingWaitlistedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
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
            ->subject('Added to Waitlist')
            ->line('You have been added to the waitlist for this workshop.')
            ->line('Reference: ' . $this->booking->reference)
            ->line('We will contact you if a space becomes available.');
    }
}
