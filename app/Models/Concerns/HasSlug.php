<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(static function (self $model): void {
            if (! $model->exists || $model->isDirty($model->slugSource()) || blank($model->getAttribute('slug'))) {
                $model->setAttribute('slug', $model->generateUniqueSlug());
            }
        });
    }

    abstract protected function slugSource(): string;

    protected function generateUniqueSlug(): string
    {
        $source = $this->getAttribute($this->slugSource());
        $base = Str::slug(is_string($source) ? $source : '');
        $base = mb_substr($base !== '' ? $base : Str::slug(class_basename($this)), 0, 180);
        $slug = $base;
        $suffix = 2;

        while ($this->newQueryWithoutScopes()
            ->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->where($this->getKeyName(), '!=', $this->getKey()))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
