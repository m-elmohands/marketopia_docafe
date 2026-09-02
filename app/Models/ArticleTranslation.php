<?php

namespace App\Models;

use App\Models\Concerns\GeneratesLocalizedSlug;
use Illuminate\Database\Eloquent\Model;

class ArticleTranslation extends Model
{
    use GeneratesLocalizedSlug;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
    ];

    protected function slugSourceAttribute(): string
    {
        return 'title';
    }
}
