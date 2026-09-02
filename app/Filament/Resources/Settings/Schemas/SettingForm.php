<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Café identity'))
                    ->schema([
                        Tabs::make('Translations / الترجمات')
                            ->tabs([
                                Tab::make('English')->schema([
                                    TextInput::make('identity.en.name')->label('Café name')->required()->maxLength(255),
                                    Textarea::make('identity.en.description')->label('Café description')->rows(4),
                                ]),
                                Tab::make('العربية')->schema([
                                    TextInput::make('identity.ar.name')->label('اسم المقهى')->required()->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                                    Textarea::make('identity.ar.description')->label('وصف المقهى')->rows(4)->extraInputAttributes(['dir' => 'rtl']),
                                ]),
                            ]),
                    ]),
                Section::make(__('Contact information'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact.email')->label(__('Email address'))->email()->required()->maxLength(255),
                        TextInput::make('contact.phone')->label(__('Phone number'))->tel()->required()->maxLength(30),
                        Textarea::make('contact.en_address')->label(__('Address in English'))->rows(3),
                        Textarea::make('contact.ar_address')->label(__('Address in Arabic'))->rows(3)->extraInputAttributes(['dir' => 'rtl']),
                    ]),
                Section::make(__('Commerce'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('commerce.delivery_fee')->label(__('Delivery fee'))->numeric()->minValue(0)->required()->suffix(__('minor units')),
                        TextInput::make('commerce.tax_percentage')->label(__('Tax percentage'))->numeric()->minValue(0)->maxValue(100)->required()->suffix('%'),
                    ]),
                Section::make(__('Social links'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('social.facebook_url')->label(__('Facebook URL'))->url()->maxLength(2048),
                        TextInput::make('social.instagram_url')->label(__('Instagram URL'))->url()->maxLength(2048),
                    ]),
            ]);
    }
}
