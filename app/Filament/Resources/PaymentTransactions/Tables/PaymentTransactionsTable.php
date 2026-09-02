<?php

namespace App\Filament\Resources\PaymentTransactions\Tables;

use App\Enums\PaymentProcessingStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paymob_transaction_id')->label('Transaction')->searchable()->copyable(),
                TextColumn::make('intention.order.order_number')->label('Order')->searchable(),
                TextColumn::make('amount_cents')->label('Amount')->money(fn ($record): string => $record->currency->value, divideBy: 100)->sortable(),
                TextColumn::make('source_type')->label('Method')->badge(),
                TextColumn::make('status')->badge(),
                IconColumn::make('is_3d_secure')->label('3DS')->boolean(),
                TextColumn::make('processed_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(PaymentProcessingStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
