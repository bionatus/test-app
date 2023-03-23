<?php

namespace App\Models;

use Database\Factories\MeteringDeviceFactory;

/**
 * @method static MeteringDeviceFactory factory()
 *
 * @mixin MeteringDevice
 */
class MeteringDevice extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'metering_device';
    /* |--- GLOBAL VARIABLES ---| */

    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
