<?php

namespace App\Filament\Resources\PaymentRefunds;

use App\Filament\Resources\PaymentRefunds\Pages\ListPaymentRefunds;
use App\Filament\Resources\PaymentRefunds\Pages\ViewPaymentRefund;
use App\Filament\Resources\PaymentRefunds\Schemas\PaymentRefundInfolist;
use App\Filament\Resources\PaymentRefunds\Tables\PaymentRefundsTable;
use App\Models\PaymentRefund;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentRefundResource extends Resource
{
    protected static ?string $model = PaymentRefund::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static ?string $recordTitleAttribute = 'paymob_transaction_id';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 3;

    public static function infolist(Schema $schema): Schema
    {
        return PaymentRefundInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentRefundsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentRefunds::route('/'),
            'view' => ViewPaymentRefund::route('/{record}'),
        ];
    }
}
