<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Paymob = 'paymob';
    case CashOnDelivery = 'cash_on_delivery';

    public function getLabel(): string
    {
        return __(match ($this) {
            self::Paymob => 'Paymob',
            self::CashOnDelivery => 'Cash on delivery',
        });
    }
}
