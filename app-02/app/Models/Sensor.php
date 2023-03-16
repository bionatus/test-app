<?php

namespace App\Models;

use Database\Factories\SensorFactory;

/**
 * @method static SensorFactory factory()
 *
 * @mixin Sensor
 */
class Sensor extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'sensor';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
