<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return __(match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
            self::Refunded => 'Refunded',
        });
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Refunded => 'info',
            self::Pending => 'warning',
        };
    }
}
