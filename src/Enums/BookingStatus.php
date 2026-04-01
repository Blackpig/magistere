<?php

namespace BlackpigCreatif\Magistere\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Waitlisted = 'waitlisted';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Waitlisted => 'Waitlisted',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
            self::NoShow => 'No Show',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'success',
            self::Waitlisted => 'info',
            self::Cancelled => 'danger',
            self::Completed => 'gray',
            self::NoShow => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Confirmed => 'heroicon-o-check-circle',
            self::Waitlisted => 'heroicon-o-queue-list',
            self::Cancelled => 'heroicon-o-x-circle',
            self::Completed => 'heroicon-o-archive-box',
            self::NoShow => 'heroicon-o-user-minus',
        };
    }

    /**
     * Returns the valid transitions from this status.
     *
     * @return array<BookingStatus>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Waitlisted, self::Cancelled],
            self::Confirmed => [self::Completed, self::NoShow, self::Cancelled],
            self::Waitlisted => [self::Confirmed, self::Cancelled],
            self::Cancelled, self::Completed, self::NoShow => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->transitions(), strict: true);
    }
}
