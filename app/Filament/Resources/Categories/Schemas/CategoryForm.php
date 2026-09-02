<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\CatalogStatus;
use App\Filament\Support\LocalizedRecordLabel;
use App\Filament\Support\LocalizedTabs;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('category-images')
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull(),
                LocalizedTabs::make(self::translationFields('en'), self::translationFields('ar'))
                    ->columnSpanFull(),
                Section::make(__('Organization and publishing'))
                    ->columns(2)
                    ->schema([
                        Select::make('parent_id')
                            ->relationship('parent', 'id', ignoreRecord: true)
                            ->getOptionLabelFromRecordUsing(fn (Category $record): string => LocalizedRecordLabel::get($record, 'name'))
                            ->preload()
                            ->placeholder(__('Top-level category')),
                        Select::make('status')->options(CatalogStatus::class)->required()->default(CatalogStatus::Draft->value),
                        TextInput::make('sort_order')->numeric()->minValue(0)->default(0)->required(),
                        Toggle::make('is_featured')->label(__('Featured')),
                    ]),
            ]);
    }

    /** @return array<int, TextInput|Textarea> */
    private static function translationFields(string $locale): array
    {
        $direction = LocalizedTabs::direction($locale);

        return [
            TextInput::make("{$locale}.name")->label(LocalizedTabs::label($locale, 'Name', 'الاسم'))->required()->maxLength(255)->extraInputAttributes($direction),
            Textarea::make("{$locale}.description")->label(LocalizedTabs::label($locale, 'Description', 'الوصف'))->rows(5)->columnSpanFull()->extraInputAttributes($direction),
            TextInput::make("{$locale}.meta_title")->label(LocalizedTabs::label($locale, 'SEO title', 'عنوان تحسين محركات البحث'))->maxLength(255)->extraInputAttributes($direction),
            Textarea::make("{$locale}.meta_description")->label(LocalizedTabs::label($locale, 'SEO description', 'وصف تحسين محركات البحث'))->rows(3)->extraInputAttributes($direction),
        ];
    }
}
