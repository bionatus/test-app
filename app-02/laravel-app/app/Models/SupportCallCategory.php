<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use Database\Factories\SupportCallCategoryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static SupportCallCategoryFactory factory()
 *
 * @mixin SupportCallCategory
 */
class SupportCallCategory extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS = 'support_call_category';
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES)->registerMediaConversions(function() {
            $this->addMediaConversion(MediaConversionNames::THUMB)->width(400)->height(400)->nonQueued();
        });
    }

    /* |--- RELATIONS ---| */

    public function children(): HasMany
    {
        return $this->hasMany(SupportCallCategory::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SupportCallCategory::class, 'parent_id');
    }

    public function instruments(): BelongsToMany
    {
        return $this->belongsToMany(Instrument::class)->withTimestamps();
    }

    public function instrumentSupportCallCategories(): HasMany
    {
        return $this->hasMany(InstrumentSupportCallCategory::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
