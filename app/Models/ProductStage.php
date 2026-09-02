<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductStage extends Model implements HasMedia, TranslatableContract
{
    use InteractsWithMedia, SoftDeletes, Translatable;

    /** @var list<string> */
    public array $translatedAttributes = ['title', 'description'];

    /** @var list<string> */
    protected $fillable = ['sort_order'];

    /** @var list<string> */
    protected $appends = ['en', 'ar'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('stage-image')->singleFile();
    }

    /** @return array<string, string|null> */
    public function getEnAttribute(): array
    {
        return $this->translationData('en');
    }

    /** @return array<string, string|null> */
    public function getArAttribute(): array
    {
        return $this->translationData('ar');
    }

    /** @return array<string, string|null> */
    private function translationData(string $locale): array
    {
        $translation = $this->translate($locale);

        return [
            'title' => $translation?->title,
            'description' => $translation?->description,
        ];
    }
}
