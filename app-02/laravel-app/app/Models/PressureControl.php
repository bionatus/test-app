<?php

namespace App\Models;

use Database\Factories\PressureControlFactory;

/**
 * @method static PressureControlFactory factory()
 *
 * @mixin PressureControl
 */
class PressureControl extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'pressure_control';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
