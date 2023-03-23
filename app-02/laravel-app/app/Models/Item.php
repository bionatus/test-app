<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static ItemFactory factory()
 *
 * @mixin Item
 */
class Item extends Model implements HasMedia
{
    use HasUuid;
    use InteractsWithMedia;

    const TYPE_PART        = 'part';
    const TYPE_SUPPLY      = 'supply';
    const TYPE_CUSTOM_ITEM = 'custom_item';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'   => 'integer',
        'uuid' => 'string',
    ];

    /* |--- FUNCTIONS ---| */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES)->registerMediaConversions(function() {
            $this->addMediaConversion(MediaConversionNames::THUMB)->width(400)->height(400)->nonQueued();
        });
    }

    public function isPart(): bool
    {
        return $this->type === self::TYPE_PART;
    }

    public function isSupply(): bool
    {
        return $this->type === self::TYPE_SUPPLY;
    }

    public function isCustomItem(): bool
    {
        return $this->type === self::TYPE_CUSTOM_ITEM;
    }

    /* |--- RELATIONS ---| */

    public function part(): HasOne
    {
        return $this->hasOne(Part::class, 'id');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    public function itemOrders(): HasMany
    {
        return $this->hasMany(ItemOrder::class);
    }

    public function orderable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'type', 'id');
    }

    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function cartOrders(): BelongsToMany
    {
        return $this->belongsToMany(CartOrder::class);
    }

    public function cartOrderItems(): HasMany
    {
        return $this->hasMany(CartOrderItem::class);
    }

    public function wishlists(): BelongsToMany
    {
        return $this->belongsToMany(Wishlist::class);
    }

    public function itemWishlists(): HasMany
    {
        return $this->hasMany(ItemWishlist::class);
    }

    public function orderSnaps(): BelongsToMany
    {
        return $this->belongsToMany(OrderSnap::class);
    }

    public function itemOrderSnaps(): HasMany
    {
        return $this->hasMany(ItemOrderSnap::class);
    }

    public function customItem(): HasOne
    {
        return $this->hasOne(CustomItem::class, 'id');
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
