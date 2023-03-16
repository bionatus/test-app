<?php

namespace App\Models;

use Database\Factories\DeviceFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static DeviceFactory factory()
 *
 * @mixin Device
 */
class Device extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function pushNotificationToken(): HasOne
    {
        return $this->hasOne(PushNotificationToken::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
