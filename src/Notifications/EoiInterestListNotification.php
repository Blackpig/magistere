<?php

namespace BlackpigCreatif\Magistere\Notifications;

use BlackpigCreatif\Magistere\Models\Workshop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EoiInterestListNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Workshop $workshop,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Workshop Update')
            ->line('We have an update on the workshop you expressed interest in.')
            ->line('Workshop: ' . $this->workshop->slug);
    }
}
