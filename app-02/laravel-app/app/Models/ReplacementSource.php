<?php

namespace App\Models;

use Database\Factories\ReplacementSourceFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static ReplacementSourceFactory factory()
 *
 * @mixin ReplacementSource
 */
class ReplacementSource extends Model
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

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
