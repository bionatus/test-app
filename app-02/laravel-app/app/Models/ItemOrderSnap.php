<?php

namespace App\Models;

use App\Casts\Money;
use Database\Factories\ItemOrderSnapFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static ItemOrderSnapFactory factory()
 *
 * @mixin ItemOrderSnap
 */
class ItemOrderSnap extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id'             => 'integer',
        'order_snap_id'  => 'integer',
        'item_id'        => 'integer',
        'order_id'       => 'integer',
        'replacement_id' => 'integer',
        'quantity'       => 'integer',
        'price'          => Money::class,
    ];

    public function orderSnap(): BelongsTo
    {
        return $this->belongsTo(OrderSnap::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(Replacement::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
