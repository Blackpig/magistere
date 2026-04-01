<?php

namespace BlackpigCreatif\Magistere\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasIcon, HasLabel
{
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';
    case Cheque = 'cheque';
    case CardManual = 'card_manual';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::BankTransfer => 'Bank Transfer',
            self::Cash => 'Cash',
            self::Cheque => 'Cheque',
            self::CardManual => 'Card (Manual)',
            self::Other => 'Other',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BankTransfer => 'heroicon-o-building-library',
            self::Cash => 'heroicon-o-banknotes',
            self::Cheque => 'heroicon-o-document-text',
            self::CardManual => 'heroicon-o-credit-card',
            self::Other => 'heroicon-o-ellipsis-horizontal-circle',
        };
    }
}
