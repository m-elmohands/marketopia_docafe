<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia, TranslatableContract
{
    use InteractsWithMedia, SoftDeletes, Translatable;

    /** @var list<string> */
    public array $translatedAttributes = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
    ];

    /** @var list<string> */
    protected $fillable = [
        'parent_id',
        'status',
        'sort_order',
        'is_featured',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $category): void {
            $parentId = $category->parent_id;

            while ($parentId !== null) {
                if ($category->exists && $parentId === $category->getKey()) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'A category cannot be its own ancestor.',
                    ]);
                }

                $parentId = self::withTrashed()->whereKey($parentId)->value('parent_id');
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }
}
