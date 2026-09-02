<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
                        Select::make('status')->options(OrderStatus::class)->required(),
                        Select::make('payment_status')->options(PaymentStatus::class)->required(),
                        Select::make('fulfillment_status')->options(FulfillmentStatus::class)->required(),
                        Textarea::make('admin_notes')->rows(5)->columnSpanFull(),
                    ]),
            ]);
    }
}
