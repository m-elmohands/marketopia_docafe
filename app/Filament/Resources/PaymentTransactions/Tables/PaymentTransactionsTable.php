<?php

namespace App\Filament\Resources\PaymentTransactions\Tables;

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
                TextColumn::make('amount_cents')->label('Amount')->money(fn ($record): string => $record->currency, divideBy: 100)->sortable(),
                TextColumn::make('source_type')->label('Method')->badge(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'succeeded' => 'success',
                    'failed' => 'danger',
                    default => 'warning',
                }),
                IconColumn::make('is_3d_secure')->label('3DS')->boolean(),
                TextColumn::make('processed_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'succeeded' => 'Succeeded',
                    'failed' => 'Failed',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
