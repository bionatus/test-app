<?php

namespace App\Models;

use Database\Factories\LevelFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static LevelFactory factory()
 *
 * @mixin Level
 */
class Level extends Model
{
    use HasSlug;

    /* |--- GLOBAL VARIABLES ---| */
    /* |--- CONSTANTS ---| */
    const SLUG_LEVEL_0 = 'level-0';
    const SLUG_LEVEL_1 = 'level-1';

    /* |--- FUNCTIONS ---| */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function isLowestLevel(): bool
    {
        return !$this->from;
    }

    public function isHighestLevel(): bool
    {
        return !$this->to;
    }

    /* |--- RELATIONS ---| */
    public function levelUsers(): HasMany
    {
        return $this->hasMany(LevelUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
