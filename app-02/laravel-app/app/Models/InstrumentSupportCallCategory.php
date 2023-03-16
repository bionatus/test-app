<?php

namespace App\Models;

use Database\Factories\InstrumentSupportCallCategoryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static InstrumentSupportCallCategoryFactory factory()
 *
 * @mixin InstrumentSupportCallCategory
 */
class InstrumentSupportCallCategory extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    public function supportCallCategory(): BelongsTo
    {
        return $this->belongsTo(SupportCallCategory::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
