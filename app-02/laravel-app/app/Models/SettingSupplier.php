<?php

namespace App\Models;

use Database\Factories\SettingSupplierFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SettingSupplierFactory factory()
 *
 * @mixin SettingSupplier
 */
class SettingSupplier extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
