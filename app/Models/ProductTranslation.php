<?php

namespace App\Models;

use App\Models\Concerns\GeneratesLocalizedSlug;
use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    use GeneratesLocalizedSlug;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'short_description',
        'description',
    ];

    protected function slugSourceAttribute(): string
    {
        return 'name';
    }
}
