<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CatalogStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return __(match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Archived => 'Archived',
        });
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Archived => 'gray',
            self::Draft => 'warning',
        };
    }
}
