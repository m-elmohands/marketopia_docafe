<?php

namespace App\Filament\Resources\Settings\Tables;

use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->searchable()->copyable()->sortable(),
                TextColumn::make('group')->badge()->sortable(),
                TextColumn::make('value')->limit(60)->wrap(),
                TextColumn::make('type')->badge(),
                IconColumn::make('is_public')->boolean(),
            ])
            ->filters([
                SelectFilter::make('group')->options(fn (): array => Setting::query()
                    ->distinct()
                    ->orderBy('group')
                    ->pluck('group', 'group')
                    ->all()),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, Setting $record): array {
                        foreach (['en', 'ar'] as $locale) {
                            $data[$locale] = $record->translate($locale)?->only(['value']) ?? [];
                        }

                        return $data;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
