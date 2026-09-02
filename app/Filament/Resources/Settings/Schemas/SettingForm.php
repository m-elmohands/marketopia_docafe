<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Setting')
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')->required()->maxLength(255)->unique(ignoreRecord: true),
                        TextInput::make('group')->required()->default('general')->maxLength(255),
                        Select::make('type')->options([
                            'string' => 'Text',
                            'integer' => 'Integer',
                            'boolean' => 'Boolean',
                            'json' => 'JSON',
                        ])->required()->default('string'),
                        Toggle::make('is_public')->label('Publicly accessible'),
                    ]),
                Section::make('Localized values')
                    ->schema([
                        Textarea::make('en.value')->label('English value')->rows(4),
                        Textarea::make('ar.value')->label('القيمة بالعربية')->rows(4)->extraInputAttributes(['dir' => 'rtl']),
                    ]),
            ]);
    }
}
