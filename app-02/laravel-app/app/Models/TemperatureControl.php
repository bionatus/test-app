<?php

namespace App\Models;

use Database\Factories\TemperatureControlFactory;

/**
 * @method static TemperatureControlFactory factory()
 *
 * @mixin TemperatureControl
 */
class TemperatureControl extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'temperature_control';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
