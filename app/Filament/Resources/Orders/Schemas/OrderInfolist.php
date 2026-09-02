<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('customer_name'),
                        TextEntry::make('customer_email')->copyable(),
                        TextEntry::make('customer_phone')->placeholder('—'),
                        TextEntry::make('shipping_address')
                            ->getStateUsing(fn ($record): string => collect($record->shipping_address)->filter()->implode(', '))
                            ->columnSpanFull(),
                    ]),
                Section::make('Totals')
                    ->columns(5)
                    ->schema([
                        TextEntry::make('subtotal')->money(fn ($record): string => $record->currency, divideBy: 100),
                        TextEntry::make('discount_total')->money(fn ($record): string => $record->currency, divideBy: 100),
                        TextEntry::make('shipping_total')->money(fn ($record): string => $record->currency, divideBy: 100),
                        TextEntry::make('tax_total')->money(fn ($record): string => $record->currency, divideBy: 100),
                        TextEntry::make('grand_total')->money(fn ($record): string => $record->currency, divideBy: 100)->weight('bold'),
                    ]),
                RepeatableEntry::make('items')
                    ->schema([
                        TextEntry::make('product_name'),
                        TextEntry::make('sku'),
                        TextEntry::make('quantity')->numeric(),
                        TextEntry::make('line_total')->money(fn ($record): string => $record->order->currency, divideBy: 100),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}
