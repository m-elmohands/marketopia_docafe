<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SettingType: string implements HasLabel
{
    case String = 'string';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Json = 'json';

    public function getLabel(): string
    {
        return __(match ($this) {
            self::String => 'Text',
            self::Integer => 'Integer',
            self::Boolean => 'Boolean',
            self::Json => 'JSON',
        });
    }
}
