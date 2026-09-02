<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Currency: string implements HasLabel
{
    case EGP = 'EGP';
    case USD = 'USD';

    public function getLabel(): string
    {
        return $this->value;
    }
}
