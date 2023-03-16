<?php

namespace App\Models;

use Database\Factories\ControlBoardFactory;

/**
 * @method static ControlBoardFactory factory()
 *
 * @mixin ControlBoard
 */
class ControlBoard extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'control_board';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
