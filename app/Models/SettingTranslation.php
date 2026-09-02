<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingTranslation extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['value'];
}
