<?php

namespace App\Models;

use Database\Factories\OrderStaffFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static OrderStaffFactory factory()
 *
 * @mixin OrderStaff
 */
class OrderStaff extends Pivot
{
    /* |--- CONSTANTS ---| */
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'order_id' => 'integer',
        'staff_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */

}
