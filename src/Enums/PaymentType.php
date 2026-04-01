<?php

namespace BlackpigCreatif\Magistere\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentType: string implements HasColor, HasLabel
{
    case Payment = 'payment';
    case Refund = 'refund';
    case Adjustment = 'adjustment';

    public function getLabel(): string
    {
        return match ($this) {
            self::Payment => 'Payment',
            self::Refund => 'Refund',
            self::Adjustment => 'Adjustment',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Payment => 'success',
            self::Refund => 'danger',
            self::Adjustment => 'warning',
        };
    }
}
