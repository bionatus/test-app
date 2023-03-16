<?php

namespace App\Models;

use Database\Factories\SupplierHourFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SupplierHourFactory factory()
 *
 * @mixin SupplierHour
 */
class SupplierHour extends Model
{
    const DAY_MONDAY            = 'monday';
    const DAY_TUESDAY           = 'tuesday';
    const DAY_WEDNESDAY         = 'wednesday';
    const DAY_THURSDAY          = 'thursday';
    const DAY_FRIDAY            = 'friday';
    const DAY_SATURDAY          = 'saturday';
    const DEFAULT_WEEK_DAY_FROM = '9:00 am';
    const DEFAULT_WEEK_DAY_TO   = '5:00 pm';
    const DEFAULT_SATURDAY_TO   = '1:00 pm';
    const MAX_WORKING_DAYS      = 21;
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
