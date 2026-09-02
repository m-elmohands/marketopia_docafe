<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\CatalogStatus;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('product-images')->circular(),
                TextColumn::make('name')->label('Product')->searchable(['sku'])->wrap(),
                TextColumn::make('sku')->searchable()->copyable(),
                TextColumn::make('price')->money(fn ($record): string => $record->currency->value, divideBy: 100)->sortable(),
                TextColumn::make('stock_quantity')->label('Stock')->numeric()->sortable(),
                TextColumn::make('status')->badge(),
                ToggleColumn::make('is_featured')->label('Featured'),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(CatalogStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data, Product $record): array => self::withTranslations(
                        $data,
                        $record,
                        ['name', 'short_description', 'description'],
                    )),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    /** @param list<string> $attributes */
    private static function withTranslations(array $data, Product $record, array $attributes): array
    {
        foreach (['en', 'ar'] as $locale) {
            $data[$locale] = $record->translate($locale)?->only($attributes) ?? [];
        }

        return $data;
    }
}
