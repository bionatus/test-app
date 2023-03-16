<?php

namespace App\Models;

use Database\Factories\ContactorFactory;

/**
 * @method static ContactorFactory factory()
 *
 * @mixin Contactor
 */
class Contactor extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'contactor';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
