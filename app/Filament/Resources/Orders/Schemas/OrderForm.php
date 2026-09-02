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
                Section::make('Order workflow')
                    ->columns(3)
                    ->schema([
                        Select::make('status')->options([
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])->required(),
                        Select::make('payment_status')->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'failed' => 'Failed',
                            'refunded' => 'Refunded',
                        ])->required(),
                        Select::make('fulfillment_status')->options([
                            'unfulfilled' => 'Unfulfilled',
                            'processing' => 'Processing',
                            'fulfilled' => 'Fulfilled',
                            'returned' => 'Returned',
                        ])->required(),
                        Textarea::make('admin_notes')->rows(5)->columnSpanFull(),
                    ]),
            ]);
    }
}
