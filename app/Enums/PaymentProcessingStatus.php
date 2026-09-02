<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentProcessingStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return __(match ($this) {
            self::Pending => 'Pending',
            self::Succeeded => 'Succeeded',
            self::Failed => 'Failed',
        });
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Succeeded => 'success',
            self::Failed => 'danger',
            self::Pending => 'warning',
        };
    }
}
