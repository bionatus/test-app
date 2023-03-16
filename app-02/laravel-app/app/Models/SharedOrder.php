<?php

namespace App\Models;

use Database\Factories\SharedOrderFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SharedOrderFactory factory()
 *
 * @mixin SharedOrder
 */
class SharedOrder extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'       => 'integer',
        'user_id'  => 'integer',
        'order_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /* |--- SCOPES ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
