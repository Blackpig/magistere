<?php

namespace BlackpigCreatif\Magistere\Notifications;

use BlackpigCreatif\Magistere\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
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
            ->subject('Booking Confirmed')
            ->line('Your booking has been confirmed.')
            ->line('Reference: ' . $this->booking->reference)
            ->line('Attendees: ' . $this->booking->attendee_count);
    }
}
