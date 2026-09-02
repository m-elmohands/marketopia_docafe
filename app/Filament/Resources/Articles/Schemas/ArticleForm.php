<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use App\Filament\Support\LocalizedTabs;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('featured_image')
                    ->collection('article-images')
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull(),
                LocalizedTabs::make(self::translationFields('en'), self::translationFields('ar'))
                    ->columnSpanFull(),
                Section::make(__('Publishing'))
                    ->columns(2)
                    ->schema([
                        Select::make('author_id')->relationship('author', 'name')->searchable()->preload(),
                        Select::make('status')->options(ArticleStatus::class)->required()->default(ArticleStatus::Draft->value),
                        DateTimePicker::make('published_at'),
                        Toggle::make('is_featured')->label(__('Featured')),
                    ])->columnSpanFull(),
            ]);
    }

    /** @return array<int, TextInput|Textarea|RichEditor> */
    private static function translationFields(string $locale): array
    {
        $direction = LocalizedTabs::direction($locale);

        return [
            TextInput::make("{$locale}.title")->label(LocalizedTabs::label($locale, 'Title', 'العنوان'))->required()->maxLength(255)->columnSpanFull()->extraInputAttributes($direction),
            Textarea::make("{$locale}.excerpt")->label(LocalizedTabs::label($locale, 'Excerpt', 'المقتطف'))->rows(3)->columnSpanFull()->extraInputAttributes($direction),
            RichEditor::make("{$locale}.content")->label(LocalizedTabs::label($locale, 'Content', 'المحتوى'))->required()->columnSpanFull()->extraInputAttributes($direction),
            TextInput::make("{$locale}.meta_title")->label(LocalizedTabs::label($locale, 'SEO title', 'عنوان تحسين محركات البحث'))->maxLength(255)->extraInputAttributes($direction),
            Textarea::make("{$locale}.meta_description")->label(LocalizedTabs::label($locale, 'SEO description', 'وصف تحسين محركات البحث'))->rows(3)->extraInputAttributes($direction),
        ];
    }
}
