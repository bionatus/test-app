<?php

namespace App\Models;

use Database\Factories\OemPartFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static OemPartFactory factory()
 *
 * @mixin OemPart
 */
class OemPart extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'oem_id'  => 'integer',
        'part_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function getRouteKeyName()
    {
        return 'uid';
    }

    /* |--- RELATIONS ---| */

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function oem(): BelongsTo
    {
        return $this->belongsTo(Oem::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
