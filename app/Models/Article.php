<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model implements TranslatableContract
{
    use SoftDeletes, Translatable;

    /** @var list<string> */
    public array $translatedAttributes = [
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
    ];

    /** @var list<string> */
    protected $fillable = [
        'author_id',
        'status',
        'is_featured',
        'published_at',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
