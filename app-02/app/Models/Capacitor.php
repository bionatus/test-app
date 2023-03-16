<?php

namespace App\Models;

use Database\Factories\CapacitorFactory;

/**
 * @method static CapacitorFactory factory()
 *
 * @mixin Capacitor
 */
class Capacitor extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'capacitor';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
