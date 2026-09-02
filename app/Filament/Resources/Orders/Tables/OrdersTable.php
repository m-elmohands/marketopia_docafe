<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
                TextColumn::make('grand_total')->money(fn ($record): string => $record->currency->value, divideBy: 100)->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('payment_status')->badge(),
                TextColumn::make('fulfillment_status')->badge(),
                TextColumn::make('placed_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(OrderStatus::class),
                SelectFilter::make('payment_status')->options(PaymentStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
