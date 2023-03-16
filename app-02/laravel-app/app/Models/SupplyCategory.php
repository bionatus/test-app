<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use Database\Factories\SupplyCategoryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static SupplyCategoryFactory factory()
 *
 * @mixin SupplyCategory
 */
class SupplyCategory extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS = 'supply_category';
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES);
    }

    public function isVisible(): bool
    {
        return !!$this->visible_at;
    }

    /* |--- RELATIONS ---| */

    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(SupplyCategory::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SupplyCategory::class, 'parent_id');
    }

    public function supplyCategoryViews(): HasMany
    {
        return $this->hasMany(SupplyCategoryView::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
