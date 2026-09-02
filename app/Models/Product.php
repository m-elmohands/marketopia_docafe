<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia, TranslatableContract
{
    use InteractsWithMedia, SoftDeletes, Translatable;

    /** @var list<string> */
    public array $translatedAttributes = [
        'name',
        'slug',
        'short_description',
        'description',
    ];

    /** @var list<string> */
    protected $fillable = [
        'sku',
        'barcode',
        'status',
        'price',
        'compare_at_price',
        'cost_price',
        'currency',
        'track_inventory',
        'stock_quantity',
        'low_stock_threshold',
        'weight_grams',
        'is_featured',
        'metadata',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'track_inventory' => 'boolean',
            'is_featured' => 'boolean',
            'metadata' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }
}
