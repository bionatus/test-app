<?php

namespace App\Models;

use Database\Factories\ItemWishListFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static ItemWishlistFactory factory()
 *
 * @mixin ItemWishlist
 */
class ItemWishlist extends Pivot
{
    use HasUuid;

    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'item_id'     => 'integer',
        'wishlist_id' => 'integer',
        'quantity'    => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(WishList::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
