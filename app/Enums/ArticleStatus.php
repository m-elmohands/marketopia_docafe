<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ArticleStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return __(match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
        });
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Archived => 'gray',
            self::Draft => 'warning',
        };
    }
}
