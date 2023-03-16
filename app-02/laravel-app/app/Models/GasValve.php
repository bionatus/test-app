<?php

namespace App\Models;

use Database\Factories\GasValveFactory;

/**
 * @method static GasValveFactory factory()
 *
 * @mixin GasValve
 */
class GasValve extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'gas_valve';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
