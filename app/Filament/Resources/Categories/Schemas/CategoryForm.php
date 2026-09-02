<?php

namespace App\Filament\Resources\Categories\Schemas;

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
                Tabs::make('Translations')
                    ->tabs([
                        Tab::make('English')->schema(self::translationFields('en')),
                        Tab::make('العربية')->schema(self::translationFields('ar')),
                    ])
                    ->columnSpanFull(),
                Section::make('Organization and publishing')
                    ->columns(2)
                    ->schema([
                        Select::make('parent_id')
                            ->relationship('parent', 'id', ignoreRecord: true)
                            ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->name)
                            ->preload()
                            ->placeholder('Top-level category'),
                        Select::make('status')->options([
                            'draft' => 'Draft',
                            'active' => 'Active',
                            'archived' => 'Archived',
                        ])->required()->default('draft'),
                        TextInput::make('sort_order')->numeric()->minValue(0)->default(0)->required(),
                        Toggle::make('is_featured')->label('Featured category'),
                    ]),
            ]);
    }

    /** @return array<int, TextInput|Textarea> */
    private static function translationFields(string $locale): array
    {
        return [
            TextInput::make("{$locale}.name")->label('Name')->required()->maxLength(255),
            Textarea::make("{$locale}.description")->label('Description')->rows(5)->columnSpanFull(),
            TextInput::make("{$locale}.meta_title")->label('SEO title')->maxLength(255),
            Textarea::make("{$locale}.meta_description")->label('SEO description')->rows(3),
        ];
    }
}
