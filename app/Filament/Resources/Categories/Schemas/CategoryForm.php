<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\CatalogStatus;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                Tabs::make('Translations / الترجمات')
                    ->tabs([
                        Tab::make('English')->extraAttributes(['dir' => 'ltr'])->schema(self::translationFields('en')),
                        Tab::make('العربية')->extraAttributes(['dir' => 'rtl'])->schema(self::translationFields('ar')),
                    ])
                    ->columnSpanFull(),
                Section::make(__('Organization and publishing'))
                    ->columns(2)
                    ->schema([
                        Select::make('parent_id')
                            ->relationship('parent', 'id', ignoreRecord: true)
                            ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->name)
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
        $labels = $locale === 'ar'
            ? [
                'name' => 'الاسم',
                'description' => 'الوصف',
                'meta_title' => 'عنوان تحسين محركات البحث',
                'meta_description' => 'وصف تحسين محركات البحث',
            ]
            : [
                'name' => 'Name',
                'description' => 'Description',
                'meta_title' => 'SEO title',
                'meta_description' => 'SEO description',
            ];
        $direction = ['dir' => $locale === 'ar' ? 'rtl' : 'ltr'];

        return [
            TextInput::make("{$locale}.name")->label($labels['name'])->required()->maxLength(255)->extraInputAttributes($direction),
            Textarea::make("{$locale}.description")->label($labels['description'])->rows(5)->columnSpanFull()->extraInputAttributes($direction),
            TextInput::make("{$locale}.meta_title")->label($labels['meta_title'])->maxLength(255)->extraInputAttributes($direction),
            Textarea::make("{$locale}.meta_description")->label($labels['meta_description'])->rows(3)->extraInputAttributes($direction),
        ];
    }
}
