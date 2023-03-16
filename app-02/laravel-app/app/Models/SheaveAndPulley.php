<?php

namespace App\Models;

use Database\Factories\SheaveAndPulleyFactory;

/**
 * @method static SheaveAndPulleyFactory factory()
 *
 * @mixin SheaveAndPulley
 */
class SheaveAndPulley extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'sheave_and_pulley';
    /* |--- GLOBAL VARIABLES ---| */

    public    $incrementing = false;
    protected $table        = 'sheaves_and_pulleys';
    public    $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
