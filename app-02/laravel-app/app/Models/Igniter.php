<?php

namespace App\Models;

use Database\Factories\IgniterFactory;

/**
 * @method static IgniterFactory factory()
 *
 * @mixin Igniter
 */
class Igniter extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'igniter';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
