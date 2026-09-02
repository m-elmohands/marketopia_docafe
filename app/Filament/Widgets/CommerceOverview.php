<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class CommerceOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return auth()->user()?->can('orders.view_any') ?? false;
    }

    protected function getStats(): array
    {
        $paidTotal = Order::query()->where('payment_status', 'paid')->sum('grand_total');

        return [
            Stat::make('Paid revenue', Number::currency($paidTotal / 100, 'EGP'))
                ->description('Captured checkout revenue')
                ->color('success'),
            Stat::make('Pending orders', Order::query()->where('status', 'pending')->count())
                ->description('Orders awaiting processing')
                ->color('warning'),
            Stat::make('Active products', Product::query()->where('status', 'active')->count())
                ->description('Products visible in the catalog')
                ->color('info'),
            Stat::make('Failed payments', PaymentTransaction::query()->where('status', 'failed')->count())
                ->description('Transactions requiring review')
                ->color('danger'),
        ];
    }
}
