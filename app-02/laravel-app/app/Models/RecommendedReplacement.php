<?php

namespace App\Models;

use Database\Factories\RecommendedReplacementFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static RecommendedReplacementFactory factory()
 *
 * @mixin RecommendedReplacement
 */
class RecommendedReplacement extends Model
{
    protected $casts = [
        'supplier_id'      => 'integer',
        'original_part_id' => 'integer',
    ];
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'original_part_id');
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
