<?php

namespace App\Models;

use Database\Factories\NoteCategoryFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static NoteCategoryFactory factory()
 *
 * @mixin NoteCategory
 */
class NoteCategory extends Model
{
    use HasSlug;

    /* |--- CONSTANTS ---| */
    const SLUG_GAMIFICATION = 'gamification';
    const SLUG_FEATURED     = 'featured';
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* |--- RELATIONS ---| */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
