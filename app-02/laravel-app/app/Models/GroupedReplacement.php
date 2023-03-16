<?php

namespace App\Models;

use Database\Factories\GroupedReplacementFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static GroupedReplacementFactory factory()
 *
 * @mixin GroupedReplacement
 */
class GroupedReplacement extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(Replacement::class, 'replacement_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'replacement_part_id');
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
