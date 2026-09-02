<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\CatalogStatus;
use App\Enums\Currency;
use App\Filament\Support\LocalizedTabs;
use App\Models\Category;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                LocalizedTabs::make(self::translationFields('en'), self::translationFields('ar'))
                    ->columnSpanFull(),
                Section::make(__('Catalog and pricing'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('sku')->required()->maxLength(255)->unique(ignoreRecord: true),
                        TextInput::make('barcode')->maxLength(255)->unique(ignoreRecord: true),
                        Select::make('status')->options(CatalogStatus::class)->required()->default(CatalogStatus::Draft->value),
                        Select::make('categories')
                            ->relationship('categories', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->name)
                            ->multiple()
                            ->preload()
                            ->label(__('Categories')),
                        TextInput::make('price')->label('Price (minor units)')->numeric()->minValue(0)->required(),
                        TextInput::make('compare_at_price')->label('Compare-at price')->numeric()->minValue(0),
                        TextInput::make('cost_price')->label('Cost price')->numeric()->minValue(0),
                        Select::make('currency')->options(Currency::class)->required()->default(Currency::EGP->value),
                        TextInput::make('weight_grams')->numeric()->minValue(0)->suffix('g'),
                        Toggle::make('is_featured')->label(__('Featured'))->columnSpanFull(),
                    ]),
                Section::make(__('Inventory and publishing'))
                    ->columns(2)
                    ->schema([
                        Toggle::make('track_inventory')->default(true)->live(),
                        TextInput::make('stock_quantity')->numeric()->minValue(0)->default(0),
                        TextInput::make('low_stock_threshold')->numeric()->minValue(0),
                        DateTimePicker::make('published_at'),
                        KeyValue::make('metadata')->columnSpanFull(),
                    ]),
                Section::make(__('Stages of Coffee Formation'))
                    ->description(__('Add and arrange the preparation stages shown for this product.'))
                    ->schema([
                        Repeater::make('stages')
                            ->relationship(
                                'stages',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->with('translations'),
                            )
                            ->orderColumn('sort_order')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemNumbers()
                            ->addActionLabel(__('Add stage'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->collection('stage-image')
                                    ->label(__('Stage image'))
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull(),
                                LocalizedTabs::make(
                                    self::stageTranslationFields('en'),
                                    self::stageTranslationFields('ar'),
                                ),
                            ])
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }

    /** @return array<int, TextInput|Textarea> */
    private static function translationFields(string $locale): array
    {
        $direction = LocalizedTabs::direction($locale);

        return [
            TextInput::make("{$locale}.name")->label(LocalizedTabs::label($locale, 'Name', 'الاسم'))->required()->maxLength(255)->extraInputAttributes($direction),
            Textarea::make("{$locale}.short_description")->label(LocalizedTabs::label($locale, 'Short description', 'الوصف المختصر'))->rows(3)->columnSpanFull()->extraInputAttributes($direction),
            Textarea::make("{$locale}.description")->label(LocalizedTabs::label($locale, 'Description', 'الوصف'))->rows(8)->columnSpanFull()->extraInputAttributes($direction),
        ];
    }

    /** @return array<int, TextInput|Textarea> */
    private static function stageTranslationFields(string $locale): array
    {
        $direction = LocalizedTabs::direction($locale);

        return [
            TextInput::make("{$locale}.title")
                ->label(LocalizedTabs::label($locale, 'Title', 'العنوان'))
                ->required()
                ->maxLength(255)
                ->extraInputAttributes($direction),
            Textarea::make("{$locale}.description")
                ->label(LocalizedTabs::label($locale, 'Description', 'الوصف'))
                ->rows(4)
                ->extraInputAttributes($direction),
        ];
    }
}
