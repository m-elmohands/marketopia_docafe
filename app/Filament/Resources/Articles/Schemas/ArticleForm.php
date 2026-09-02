<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                Tabs::make('Translations / الترجمات')
                    ->tabs([
                        Tab::make('English')->schema(self::translationFields('en')),
                        Tab::make('العربية')->schema(self::translationFields('ar')),
                    ])
                    ->columnSpanFull(),
                Section::make(__('Publishing'))
                    ->columns(2)
                    ->schema([
                        Select::make('author_id')->relationship('author', 'name')->searchable()->preload(),
                        Select::make('status')->options(ArticleStatus::class)->required()->default(ArticleStatus::Draft->value),
                        DateTimePicker::make('published_at'),
                        Toggle::make('is_featured')->label(__('Featured')),
                    ]),
            ]);
    }

    /** @return array<int, TextInput|Textarea|RichEditor> */
    private static function translationFields(string $locale): array
    {
        $labels = $locale === 'ar'
            ? [
                'title' => 'العنوان',
                'excerpt' => 'المقتطف',
                'content' => 'المحتوى',
                'meta_title' => 'عنوان تحسين محركات البحث',
                'meta_description' => 'وصف تحسين محركات البحث',
            ]
            : [
                'title' => 'Title',
                'excerpt' => 'Excerpt',
                'content' => 'Content',
                'meta_title' => 'SEO title',
                'meta_description' => 'SEO description',
            ];
        $direction = ['dir' => $locale === 'ar' ? 'rtl' : 'ltr'];

        return [
            TextInput::make("{$locale}.title")->label($labels['title'])->required()->maxLength(255)->columnSpanFull()->extraInputAttributes($direction),
            Textarea::make("{$locale}.excerpt")->label($labels['excerpt'])->rows(3)->columnSpanFull()->extraInputAttributes($direction),
            RichEditor::make("{$locale}.content")->label($labels['content'])->required()->columnSpanFull()->extraInputAttributes($direction),
            TextInput::make("{$locale}.meta_title")->label($labels['meta_title'])->maxLength(255)->extraInputAttributes($direction),
            Textarea::make("{$locale}.meta_description")->label($labels['meta_description'])->rows(3)->extraInputAttributes($direction),
        ];
    }
}
