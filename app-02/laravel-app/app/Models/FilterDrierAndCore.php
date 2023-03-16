<?php

namespace App\Models;

use Database\Factories\FilterDrierAndCoreFactory;

/**
 * @method static FilterDrierAndCoreFactory factory()
 *
 * @mixin FilterDrierAndCore
 */
class FilterDrierAndCore extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'filter_drier_and_core';
    /* |--- GLOBAL VARIABLES ---| */

    public    $incrementing = false;
    protected $table        = 'filter_driers_and_cores';
    public    $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
