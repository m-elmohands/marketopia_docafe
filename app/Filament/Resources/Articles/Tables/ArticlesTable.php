<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ArticleStatus;
use App\Models\Article;
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

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('article-images'),
                TextColumn::make('title')->label('Article')->wrap(),
                TextColumn::make('author.name')->searchable()->sortable(),
                TextColumn::make('status')->badge(),
                ToggleColumn::make('is_featured')->label('Featured'),
                TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ArticleStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data, Article $record): array => self::withTranslations(
                        $data,
                        $record,
                        ['title', 'excerpt', 'content', 'meta_title', 'meta_description'],
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
    private static function withTranslations(array $data, Article $record, array $attributes): array
    {
        foreach (['en', 'ar'] as $locale) {
            $data[$locale] = $record->translate($locale)?->only($attributes) ?? [];
        }

        return $data;
    }
}
