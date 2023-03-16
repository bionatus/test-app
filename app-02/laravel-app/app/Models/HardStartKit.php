<?php

namespace App\Models;

use Database\Factories\HardStartKitFactory;

/**
 * @method static HardStartKitFactory factory()
 *
 * @mixin HardStartKit
 */
class HardStartKit extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'hard_start_kit';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
