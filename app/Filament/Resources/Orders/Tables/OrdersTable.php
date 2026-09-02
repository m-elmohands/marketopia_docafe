<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->copyable()->sortable(),
                TextColumn::make('customer_name')->searchable(),
                TextColumn::make('grand_total')->money(fn ($record): string => $record->currency, divideBy: 100)->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('payment_status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'failed' => 'danger',
                    'refunded' => 'info',
                    default => 'warning',
                }),
                TextColumn::make('fulfillment_status')->badge(),
                TextColumn::make('placed_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]),
                SelectFilter::make('payment_status')->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
