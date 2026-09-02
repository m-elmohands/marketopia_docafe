<?php

namespace App\Filament\Resources\PaymentTransactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Paymob transaction')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('paymob_transaction_id')->copyable(),
                        TextEntry::make('paymob_order_id')->copyable(),
                        TextEntry::make('integration_id')->copyable(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('amount_cents')->money(fn ($record): string => $record->currency->value, divideBy: 100),
                        TextEntry::make('source_type'),
                        TextEntry::make('source_subtype'),
                        TextEntry::make('masked_pan'),
                        TextEntry::make('processed_at')->dateTime(),
                        TextEntry::make('error_message')->columnSpanFull()->placeholder('No error'),
                    ]),
            ]);
    }
}
