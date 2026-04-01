<?php

namespace BlackpigCreatif\Magistere\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Unpaid = 'unpaid';
    case DepositReceived = 'deposit_received';
    case PartPaid = 'part_paid';
    case Paid = 'paid';
    case Overpaid = 'overpaid';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::DepositReceived => 'Deposit Received',
            self::PartPaid => 'Part Paid',
            self::Paid => 'Paid',
            self::Overpaid => 'Overpaid',
            self::Refunded => 'Refunded',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Unpaid => 'danger',
            self::DepositReceived => 'warning',
            self::PartPaid => 'warning',
            self::Paid => 'success',
            self::Overpaid => 'warning',
            self::Refunded => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Unpaid => 'heroicon-o-exclamation-circle',
            self::DepositReceived => 'heroicon-o-banknotes',
            self::PartPaid => 'heroicon-o-banknotes',
            self::Paid => 'heroicon-o-check-circle',
            self::Overpaid => 'heroicon-o-exclamation-triangle',
            self::Refunded => 'heroicon-o-arrow-uturn-left',
        };
    }
}
