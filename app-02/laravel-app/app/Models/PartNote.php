<?php

namespace App\Models;

use Database\Factories\PartNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static PartNoteFactory factory()
 *
 * @mixin PartNote
 */
class PartNote extends Model
{
    use HasFactory;

    /* |--- GLOBAL VARIABLES ---| */
    public $timestamps = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
