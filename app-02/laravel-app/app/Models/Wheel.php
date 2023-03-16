<?php

namespace App\Models;

use Database\Factories\WheelFactory;

/**
 * @method static WheelFactory factory()
 *
 * @mixin Wheel
 */
class Wheel extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'wheel';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
