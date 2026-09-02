<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FulfillmentStatus: string implements HasLabel
{
    case Unfulfilled = 'unfulfilled';
    case Processing = 'processing';
    case Fulfilled = 'fulfilled';
    case Returned = 'returned';

    public function getLabel(): string
    {
        return __(match ($this) {
            self::Unfulfilled => 'Unfulfilled',
            self::Processing => 'Processing',
            self::Fulfilled => 'Fulfilled',
            self::Returned => 'Returned',
        });
    }
}
