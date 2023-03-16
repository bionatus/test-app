<?php

namespace App\Models;

use Database\Factories\OtherFactory;

/**
 * @method static OtherFactory factory()
 *
 * @mixin Other
 */
class Other extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'other';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
