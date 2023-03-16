<?php

namespace App\Models;

use Database\Factories\ReplacementNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static ReplacementNoteFactory factory()
 *
 * @mixin ReplacementNote
 */
class ReplacementNote extends Model
{
    use HasFactory;

    /* |--- GLOBAL VARIABLES ---| */
    public $timestamps = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(Replacement::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
