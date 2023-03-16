<?php

namespace App\Models;

use Database\Factories\MissedOrderRequestFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static MissedOrderRequestFactory factory()
 *
 * @mixin MissedOrderRequest
 */
class MissedOrderRequest extends Model
{
    /* |--- RELATIONS ---| */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
