<?php

namespace App\Models;

use Database\Factories\SingleReplacementFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SingleReplacementFactory factory()
 *
 * @mixin SingleReplacement
 */
class SingleReplacement extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    public $incrementing = false;
    public $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(Replacement::class, 'id', '');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'replacement_part_id');
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
