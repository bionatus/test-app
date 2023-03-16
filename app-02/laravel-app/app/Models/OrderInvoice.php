<?php

namespace App\Models;

use App\Casts\Money;
use Database\Factories\OrderInvoiceFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static OrderInvoiceFactory factory()
 *
 * @mixin OrderInvoice
 */
class OrderInvoice extends Model
{
    use HasUuid;

    /* |--- CONSTANTS ---| */
    const TYPE_CREDIT  = 'credit';
    const TYPE_INVOICE = 'invoice';
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id'           => 'integer',
        'uuid'         => 'string',
        'subtotal'     => Money::class,
        'processed_at' => 'datetime',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
