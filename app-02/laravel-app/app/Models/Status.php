<?php

namespace App\Models;

use Database\Factories\StatusFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static StatusFactory factory()
 *
 * @mixin Status
 */
class Status extends Model
{
    /* |--- CONSTANTS ---| */
    const STATUS_PENDING               = 100;
    const STATUS_PENDING_APPROVAL      = 200;
    const STATUS_APPROVED              = 300;
    const STATUS_COMPLETED             = 400;
    const STATUS_CANCELED              = 500;
    const STATUS_NAME_PENDING          = 'pending';
    const STATUS_NAME_PENDING_APPROVAL = 'pending_approval';
    const STATUS_NAME_APPROVED         = 'approved';
    const STATUS_NAME_COMPLETED        = 'completed';
    const STATUS_NAME_CANCELED         = 'canceled';
    const STATUSES_NAME                = [
        self::STATUS_NAME_PENDING,
        self::STATUS_NAME_PENDING_APPROVAL,
        self::STATUS_NAME_APPROVED,
        self::STATUS_NAME_COMPLETED,
        self::STATUS_NAME_CANCELED,
    ];
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /* |--- RELATIONS ---| */

    public function substatuses(): HasMany
    {
        return $this->hasMany(Substatus::class);
    }
}
