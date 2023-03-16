<?php

namespace App\Models;

use Database\Factories\BrandSupplierFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static BrandSupplierFactory factory()
 *
 * @mixin BrandSupplier
 */
class BrandSupplier extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */

}
