<?php

namespace App\Models;

use Database\Factories\MotorFactory;

/**
 * @method static MotorFactory factory()
 *
 * @mixin Motor
 */
class Motor extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'motor';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
