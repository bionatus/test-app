<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use Database\Factories\SupplyFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static SupplyFactory factory()
 *
 * @mixin Supply
 */
class Supply extends Model implements IsOrderable, HasMedia
{
    use InteractsWithMedia;

    const MORPH_ALIAS = 'supply';
    /* |--- GLOBAL VARIABLES ---| */

    public    $incrementing = false;
    public    $timestamps   = false;
    protected $casts        = [
        'id'                 => 'integer',
        'supply_category_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */
    public function getCategoryMedia(): ?Media
    {
        if (!$supplyCategory = $this->supplyCategory) {
            return null;
        }

        return $supplyCategory->getFirstMedia(MediaCollectionNames::IMAGES);
    }

    /* |--- RELATIONS ---| */

    public function item(): HasOne
    {
        return $this->hasOne(Item::class, 'id');
    }

    public function supplyCategory(): BelongsTo
    {
        return $this->belongsTo(SupplyCategory::class);
    }

    public function cartSupplyCounters(): HasMany
    {
        return $this->hasMany(CartSupplyCounter::class);
    }

    /* |--- ACCESSORS ---| */
    public function getReadableTypeAttribute(): string
    {
        return $this->name;
    }
    /* |--- MUTATORS ---| */
}
