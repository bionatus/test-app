<?php

namespace App\Models;

use Database\Factories\AirFilterFactory;

/**
 * @method static AirFilterFactory factory()
 *
 * @mixin AirFilter
 */
class AirFilter extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'air_filter';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
