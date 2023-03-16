<?php

namespace App\Models;

use Database\Factories\SettingStaffFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SettingStaffFactory factory()
 *
 * @mixin SettingStaff
 */
class SettingStaff extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
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
