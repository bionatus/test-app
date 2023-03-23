<?php

namespace App\Models;

use App\Constants\OperatingSystems;
use Database\Factories\PushNotificationTokenFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static PushNotificationTokenFactory factory()
 *
 * @mixin PushNotificationToken
 */
class PushNotificationToken extends Model
{
    use HasUuid;

    const OS_ANDROID = OperatingSystems::ANDROID;
    const OS_IOS     = OperatingSystems::IOS;
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'              => 'integer',
        'uuid'            => 'string',
        'device_token_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
