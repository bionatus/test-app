<?php

namespace App\Models;

use Database\Factories\ShipmentDeliveryPreferenceFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static ShipmentDeliveryPreferenceFactory factory()
 *
 * @mixin ShipmentDeliveryPreference
 */
class ShipmentDeliveryPreference extends Model
{
    use HasSlug;

    /* |--- CONSTANTS ---| */
    const PREFERENCE_OVERNIGHT      = 100;
    const PREFERENCE_PRIORITY       = 200;
    const PREFERENCE_STANDARD       = 300;
    const PREFERENCE_SLUG_OVERNIGHT = 'overnight';
    const PREFERENCE_SLUG_PRIORITY  = 'priority';
    const PREFERENCE_SLUG_STANDARD  = 'standard';
    const PREFERENCES_SLUG          = [
        self::PREFERENCE_SLUG_OVERNIGHT,
        self::PREFERENCE_SLUG_PRIORITY,
        self::PREFERENCE_SLUG_STANDARD,
    ];
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
    public function shipmentDelivery(): HasMany
    {
        return $this->hasMany(ShipmentDelivery::class, 'shipment_delivery_preference_id');
    }
}

