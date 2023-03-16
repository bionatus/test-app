<?php

namespace App\Models;

use Database\Factories\ServiceTokenFactory;

/**
 * @method static ServiceTokenFactory factory()
 *
 * @mixin ServiceToken
 */
class ServiceToken extends Model
{
    /* |--- CONSTANTS ---| */
    const ACCESS_TOKEN  = 'access_token';
    const REFRESH_TOKEN = 'refresh_token';
    const XOXO          = 'xoxo';
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- RELATIONS ---| */
}
