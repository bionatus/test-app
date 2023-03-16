<?php

namespace App\Models;

use Database\Factories\OemUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static OemUserFactory factory()
 *
 * @mixin OemUser
 */
class OemUser extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'      => 'integer',
        'oem_id'  => 'integer',
        'user_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function oem(): BelongsTo
    {
        return $this->belongsTo(Oem::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
