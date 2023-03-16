<?php

namespace App\Models;

use Database\Factories\FanBladeFactory;

/**
 * @method static FanBladeFactory factory()
 *
 * @mixin FanBlade
 */
class FanBlade extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'fan_blade';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
