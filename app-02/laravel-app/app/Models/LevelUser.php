<?php

namespace App\Models;

use Database\Factories\LevelUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static LevelUserFactory factory()
 *
 * @mixin LevelUser
 */
class LevelUser extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- CONSTANTS ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
