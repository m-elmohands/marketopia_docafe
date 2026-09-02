<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Enums\CatalogStatus;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
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

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('category-images')->circular(),
                TextColumn::make('name')->label('Category')->wrap(),
                TextColumn::make('parent.name')->label('Parent')->placeholder('Top level'),
                TextColumn::make('products_count')->counts('products')->label('Products')->numeric()->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('sort_order')->numeric()->sortable(),
                ToggleColumn::make('is_featured')->label('Featured'),
            ])
            ->filters([
                SelectFilter::make('status')->options(CatalogStatus::class),
                TrashedFilter::make(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data, Category $record): array => self::withTranslations(
                        $data,
                        $record,
                        ['name', 'description', 'meta_title', 'meta_description'],
                    )),
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
    private static function withTranslations(array $data, Category $record, array $attributes): array
    {
        foreach (['en', 'ar'] as $locale) {
            $data[$locale] = $record->translate($locale)?->only($attributes) ?? [];
        }

        return $data;
    }
}
