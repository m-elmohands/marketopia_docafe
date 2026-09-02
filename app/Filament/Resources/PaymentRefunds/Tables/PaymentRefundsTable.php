<?php

namespace App\Filament\Resources\PaymentRefunds\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentRefundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paymob_transaction_id')->label('Refund transaction')->searchable()->copyable()->placeholder('Pending'),
                TextColumn::make('transaction.paymob_transaction_id')->label('Original transaction')->searchable()->copyable(),
                TextColumn::make('amount_cents')->label('Amount')->money(fn ($record): string => $record->currency, divideBy: 100)->sortable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'succeeded' => 'success',
                    'failed' => 'danger',
                    default => 'warning',
                }),
                TextColumn::make('requestedBy.name')->label('Requested by'),
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
