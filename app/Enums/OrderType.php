<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrderType: string implements HasLabel
{
    case DineIn = 'dine_in';
    case Takeaway = 'takeaway';
    case Pickup = 'pickup';

    public function getLabel(): string
    {
        return __(match ($this) {
            self::DineIn => 'Dine in',
            self::Takeaway => 'Takeaway',
            self::Pickup => 'Pickup',
        });
    }
}
