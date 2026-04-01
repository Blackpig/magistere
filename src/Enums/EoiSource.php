<?php

namespace BlackpigCreatif\Magistere\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EoiSource: string implements HasColor, HasIcon, HasLabel
{
    case Interest = 'interest';
    case Waitlist = 'waitlist';

    public function getLabel(): string
    {
        return match ($this) {
            self::Interest => 'Interest',
            self::Waitlist => 'Waitlist',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Interest => 'info',
            self::Waitlist => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Interest => 'heroicon-o-hand-raised',
            self::Waitlist => 'heroicon-o-queue-list',
        };
    }
}
