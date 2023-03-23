<?php

namespace App\Models;

use Database\Factories\SettingUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SettingUserFactory factory()
 *
 * @mixin SettingUser
 */
class SettingUser extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setting(): BelongsTo
    {
        return $this->belongsTo(Setting::class);
    }

    /* |--- ACCESSORS ---| */
    public function getValueAttribute($value)
    {
        switch ($this->setting->type) {
            case Setting::TYPE_BOOLEAN:
                return (bool) $value;
            default:
                return $value;
        }
    }
    /* |--- MUTATORS ---| */
}
