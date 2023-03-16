<?php

namespace App\Models;

use Database\Factories\AppSettingFactory;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static AppSettingFactory factory()
 *
 * @mixin AppSetting
 */
class AppSetting extends Model
{
    use HasSlug;

    const TYPE_STRING                      = 'string';
    const TYPE_INTEGER                     = 'integer';
    const SLUG_SEARCH_BY_PART_IFRAME_URL   = 'search-by-part-iframe-url';
    const SLUG_BLUON_POINTS_MULTIPLIER     = 'bluon-points-multiplier';
    const SLUG_TECHNICIAN_ONBOARDING_VIDEO = 'technician-onboarding-video';
    const SLUG_HOME_SCREEN_VIDEO           = 'home-screen-video';
    const SLUG_TUTORIAL_VIDEO              = 'tutorial-video';
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('label')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
