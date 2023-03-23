<?php

namespace App\Models;

use Database\Factories\CartOrderFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static CartOrderFactory factory()
 *
 * @mixin CartOrder
 */
class CartOrder extends Model
{
    /* |--- CONSTANTS ---| */
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'       => 'integer',
        'order_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }

    public function cartOrderItems(): HasMany
    {
        return $this->hasMany(CartOrderItem::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
