<?php

namespace App\Filament\Resources\Articles\Schemas;

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
                Tabs::make('Translations')
                    ->tabs([
                        Tab::make(__('English content'))->schema(self::translationFields('en')),
                        Tab::make(__('Arabic content'))->schema(self::translationFields('ar')),
                    ])
                    ->columnSpanFull(),
                Section::make(__('Publishing'))
                    ->columns(2)
                    ->schema([
                        Select::make('author_id')->relationship('author', 'name')->searchable()->preload(),
                        Select::make('status')->options([
                            'draft' => __('Draft'),
                            'published' => __('Published'),
                            'archived' => __('Archived'),
                        ])->required()->default('draft'),
                        DateTimePicker::make('published_at'),
                        Toggle::make('is_featured')->label(__('Featured')),
                    ]),
            ]);
    }

    /** @return array<int, TextInput|Textarea|RichEditor> */
    private static function translationFields(string $locale): array
    {
        return [
            TextInput::make("{$locale}.title")->label(__('Title'))->required()->maxLength(255)->columnSpanFull(),
            Textarea::make("{$locale}.excerpt")->label(__('Excerpt'))->rows(3)->columnSpanFull(),
            RichEditor::make("{$locale}.content")->label(__('Content field'))->required()->columnSpanFull(),
            TextInput::make("{$locale}.meta_title")->label(__('SEO title'))->maxLength(255),
            Textarea::make("{$locale}.meta_description")->label(__('SEO description'))->rows(3),
        ];
    }
}
