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
                Section::make(__('Setting'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')->required()->maxLength(255)->unique(ignoreRecord: true),
                        TextInput::make('group')->required()->default('general')->maxLength(255),
                        Select::make('type')->options([
                            'string' => __('Text'),
                            'integer' => __('Integer'),
                            'boolean' => __('Boolean'),
                            'json' => __('JSON'),
                        ])->required()->default('string'),
                        Toggle::make('is_public')->label(__('Publicly accessible')),
                    ]),
                Section::make(__('Localized values'))
                    ->schema([
                        Textarea::make('en.value')->label(__('English value'))->rows(4),
                        Textarea::make('ar.value')->label(__('Arabic value'))->rows(4)->extraInputAttributes(['dir' => 'rtl']),
                    ]),
            ]);
    }
}
