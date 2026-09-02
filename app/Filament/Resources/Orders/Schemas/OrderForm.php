<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Order workflow'))
                    ->columns(3)
                    ->schema([
                        Select::make('status')->options([
                            'pending' => __('Pending'),
                            'processing' => __('Processing'),
                            'completed' => __('Completed'),
                            'cancelled' => __('Cancelled'),
                        ])->required(),
                        Select::make('payment_status')->options([
                            'pending' => __('Pending'),
                            'paid' => __('Paid'),
                            'failed' => __('Failed'),
                            'refunded' => __('Refunded'),
                        ])->required(),
                        Select::make('fulfillment_status')->options([
                            'unfulfilled' => __('Unfulfilled'),
                            'processing' => __('Processing'),
                            'fulfilled' => __('Fulfilled'),
                            'returned' => __('Returned'),
                        ])->required(),
                        Textarea::make('admin_notes')->rows(5)->columnSpanFull(),
                    ]),
            ]);
    }
}
