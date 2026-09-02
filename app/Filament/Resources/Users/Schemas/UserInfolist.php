<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Account'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')->copyable(),
                        TextEntry::make('roles.name')->badge(),
                        TextEntry::make('email_verified_at')->dateTime()->placeholder(__('Not verified')),
                        TextEntry::make('created_at')->dateTime(),
                    ]),
            ]);
    }
}
