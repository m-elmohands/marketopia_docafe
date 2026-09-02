<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
    ];
}
