<?php

namespace App\Models;

use App\Enums\SettingType;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model implements TranslatableContract
{
    use Translatable;

    /** @var list<string> */
    public array $translatedAttributes = ['value'];

    /** @var list<string> */
    protected $fillable = [
        'group',
        'key',
        'type',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
            'is_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        $clearApiCache = fn () => collect(['en', 'ar'])->each(fn (string $locale) => Cache::forget("api.settings.{$locale}"));

        static::saved($clearApiCache);
        static::deleted($clearApiCache);
    }
}
