<?php

namespace App\Models;

use Database\Factories\SupplierCompanyFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static SupplierCompanyFactory factory()
 *
 * @mixin SupplierCompany
 */
class SupplierCompany extends Model
{
    /* |--- CONSTANTS ---| */
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function supplier(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
