<?php

namespace App\Models;

use Database\Factories\LayoutFactory;

/**
 * @method static LayoutFactory factory()
 *
 * @mixin Layout
 */
class Layout extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $table      = 'layouts';
    public    $timestamps = false;
    protected $casts      = [
        'products'   => 'array',
        'conversion' => 'array',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */

}
