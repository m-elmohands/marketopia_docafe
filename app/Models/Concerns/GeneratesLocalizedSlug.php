<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait GeneratesLocalizedSlug
{
    protected static function bootGeneratesLocalizedSlug(): void
    {
        static::saving(function (Model $translation): void {
            $source = (string) $translation->getAttribute($translation->slugSourceAttribute());
            $baseSlug = $slug = $translation->makeLocalizedSlug($source);
            $suffix = 2;

            while ($translation->slugAlreadyExists($slug)) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            $translation->setAttribute('slug', $slug);
        });
    }

    abstract protected function slugSourceAttribute(): string;

    private function makeLocalizedSlug(string $source): string
    {
        $slug = Str::lower($source);
        $slug = preg_replace('/[^\pL\pN]+/u', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'item';
    }

    private function slugAlreadyExists(string $slug): bool
    {
        return static::query()
            ->where('locale', $this->getAttribute('locale'))
            ->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->exists();
    }
}
