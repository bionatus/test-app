<?php

namespace App\Models;

use Database\Factories\CartSupplyCounterFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static CartSupplyCounterFactory factory()
 *
 * @mixin CartSupplyCounter
 */
class CartSupplyCounter extends Model
{
    /* |--- CONSTANTS ---| */
    /* |--- GLOBAL VARIABLES ---| */
    public    $table = 'cart_supply_counter';
    protected $casts = [
        'user_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
