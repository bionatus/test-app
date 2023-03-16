<?php

namespace App\Models;

use Database\Factories\PartBrandFactory;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static PartBrandFactory factory()
 *
 * @mixin PartBrand
 */
class PartBrand extends Model
{
    use InteractsWithMedia;
    use HasSlug;

    const MORPH_ALIAS = 'part_brand';
    /* |--- GLOBAL VARIABLES ---| */

    /* |--- FUNCTIONS ---| */

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
