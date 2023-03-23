<?php

namespace App\Models;

use Database\Factories\BeltFactory;

/**
 * @method static BeltFactory factory()
 *
 * @mixin Belt
 */
class Belt extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'belt';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
