<?php

namespace App\Models;

use Database\Factories\CartOrderItemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static CartOrderItemFactory factory()
 *
 * @mixin CartOrderItem
 */
class CartOrderItem extends Pivot
{
    /* |--- CONSTANTS ---| */
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'item_id'       => 'integer',
        'cart_order_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function cartOrder(): BelongsTo
    {
        return $this->belongsTo(CartOrder::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
