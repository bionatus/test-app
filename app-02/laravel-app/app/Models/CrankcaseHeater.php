<?php

namespace App\Models;

use Database\Factories\CrankcaseHeaterFactory;

/**
 * @method static CrankcaseHeaterFactory factory()
 *
 * @mixin CrankcaseHeater
 */
class CrankcaseHeater extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'crankcase_heater';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
