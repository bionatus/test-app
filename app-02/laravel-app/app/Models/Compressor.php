<?php

namespace App\Models;

use Database\Factories\CompressorFactory;

/**
 * @method static CompressorFactory factory()
 *
 * @mixin Compressor
 */
class Compressor extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'compressor';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
