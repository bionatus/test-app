<?php

namespace App\Models;

use Database\Factories\PointFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static PointFactory factory()
 *
 * @mixin Point
 */
class Point extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- CONSTANTS ---| */
    const ACTION_ADJUSTMENT         = 'adjustment';
    const ACTION_ORDER_APPROVED     = 'order_approved';
    const ACTION_ORDER_CANCELED     = 'order_canceled';
    const ACTION_ORDER_COMPLETED    = 'order_completed';
    const ACTION_ORDER_ITEM_REMOVED = 'item_removed';
    const ACTION_REDEEMED           = 'redeemed';
    const CASH_VALUE                = 0.01;
    const POLYMORPHIC_NAME          = 'object';
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function object(): MorphTo
    {
        return $this->morphTo();
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
