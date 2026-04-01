<?php

namespace BlackpigCreatif\Magistere\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExtraPer: string implements HasLabel
{
    case Booking = 'booking';
    case Attendee = 'attendee';

    public function getLabel(): string
    {
        return match ($this) {
            self::Booking => 'Per Booking',
            self::Attendee => 'Per Attendee',
        };
    }
}
