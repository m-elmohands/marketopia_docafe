<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStageTranslation extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['title', 'description'];
}
