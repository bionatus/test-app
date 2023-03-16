<?php

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static BrandFactory factory()
 *
 * @mixin Brand
 */
class Brand extends Model
{
    use InteractsWithMedia;
    use HasSlug;

    const MORPH_ALIAS = 'brand';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'           => 'integer',
        'logo'         => 'json',
        'series_count' => 'integer',
    ];

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

    public function series(): HasMany
    {
        return $this->hasMany(Series::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function brandSuppliers(): HasMany
    {
        return $this->hasMany(BrandSupplier::class);
    }

    public function brandDetailCounters(): HasMany
    {
        return $this->hasMany(BrandDetailCounter::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, BrandDetailCounter::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, BrandDetailCounter::class)->withTimestamps();
    }

    public function supportCalls(): HasMany
    {
        return $this->hasMany(SupportCall::class, 'missing_oem_brand_id', 'id');
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
