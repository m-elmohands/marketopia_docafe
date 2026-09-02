<?php

namespace App\Models;

use App\Models\Concerns\GeneratesLocalizedSlug;
use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    use GeneratesLocalizedSlug;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected function slugSourceAttribute(): string
    {
        return 'name';
    }
}
