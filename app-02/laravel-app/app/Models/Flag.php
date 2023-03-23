<?php

namespace App\Models;

use Database\Factories\FlagFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static FlagFactory factory()
 *
 * @mixin Flag
 */
class Flag extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    const APP_VERSION_CONFIRM   = 'app-version-confirm-:app_version';
    const FORBIDDEN_CURRI       = 'forbidden-curri';
    const SUPPORT_CALL_DISABLED = 'support-call-disabled';
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function flaggable(): MorphTo
    {
        return $this->morphTo();
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
