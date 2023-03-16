<?php

namespace App\Models;

use Database\Factories\SupplierUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SupplierUserFactory factory()
 *
 * @mixin SupplierUser
 */
class SupplierUser extends Pivot
{
    /* |--- CONSTANTS ---| */

    const STATUS_CONFIRMED   = 'confirmed';
    const STATUS_UNCONFIRMED = 'unconfirmed';
    const STATUS_REMOVED     = 'removed';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'              => 'integer',
        'supplier_id'     => 'integer',
        'user_id'         => 'integer',
        'cash_buyer'      => 'boolean',
        'visible_by_user' => 'boolean',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
