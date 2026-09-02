<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('images')
                    ->collection('product-images')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->imageEditor()
                    ->columnSpanFull(),
                Tabs::make('Translations')
                    ->tabs([
                        Tab::make('English')->schema(self::translationFields('en')),
                        Tab::make('العربية')->schema(self::translationFields('ar')),
                    ])
                    ->columnSpanFull(),
                Section::make('Catalog and pricing')
                    ->columns(3)
                    ->schema([
                        TextInput::make('sku')->required()->maxLength(255)->unique(ignoreRecord: true),
                        TextInput::make('barcode')->maxLength(255)->unique(ignoreRecord: true),
                        Select::make('status')->options([
                            'draft' => 'Draft',
                            'active' => 'Active',
                            'archived' => 'Archived',
                        ])->required()->default('draft'),
                        Select::make('categories')
                            ->relationship('categories', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->name)
                            ->multiple()
                            ->preload()
                            ->label('Categories'),
                        TextInput::make('price')->label('Price (minor units)')->numeric()->minValue(0)->required(),
                        TextInput::make('compare_at_price')->label('Compare-at price')->numeric()->minValue(0),
                        TextInput::make('cost_price')->label('Cost price')->numeric()->minValue(0),
                        Select::make('currency')->options(['EGP' => 'EGP', 'USD' => 'USD'])->required()->default('EGP'),
                        TextInput::make('weight_grams')->numeric()->minValue(0)->suffix('g'),
                        Toggle::make('is_featured')->label('Featured product'),
                    ]),
                Section::make('Inventory and publishing')
                    ->columns(3)
                    ->schema([
                        Toggle::make('track_inventory')->default(true)->live(),
                        TextInput::make('stock_quantity')->numeric()->minValue(0)->default(0),
                        TextInput::make('low_stock_threshold')->numeric()->minValue(0),
                        DateTimePicker::make('published_at'),
                        KeyValue::make('metadata')->columnSpanFull(),
                    ]),
            ]);
    }

    /** @return array<int, TextInput|Textarea> */
    private static function translationFields(string $locale): array
    {
        return [
            TextInput::make("{$locale}.name")->label('Name')->required()->maxLength(255),
            Textarea::make("{$locale}.short_description")->label('Short description')->rows(3)->columnSpanFull(),
            Textarea::make("{$locale}.description")->label('Description')->rows(8)->columnSpanFull(),
        ];
    }
}
