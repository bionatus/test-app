<?php

namespace App\Models;

use Database\Factories\OrderLockedDataFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static OrderLockedDataFactory factory()
 *
 * @mixin OrderLockedData
 */
class OrderLockedData extends Model
{
    use HasFactory;

    /* |--- GLOBAL VARIABLES ---| */
    public $table = 'orders_locked_data';
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
