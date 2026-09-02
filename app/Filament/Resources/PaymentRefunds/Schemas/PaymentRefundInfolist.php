<?php

namespace App\Filament\Resources\PaymentRefunds\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentRefundInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Refund')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('paymob_transaction_id')->copyable()->placeholder('Pending'),
                        TextEntry::make('transaction.paymob_transaction_id')->label('Original transaction')->copyable(),
                        TextEntry::make('amount_cents')->label('Amount')->money(fn ($record): string => $record->currency->value, divideBy: 100),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('requestedBy.name')->label('Requested by'),
                        TextEntry::make('processed_at')->dateTime()->placeholder('Not processed'),
                        TextEntry::make('reason')->columnSpanFull()->placeholder('No reason provided'),
                    ]),
            ]);
    }
}
