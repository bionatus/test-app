<?php

namespace App\Models;

use Database\Factories\UserTaggableFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static UserTaggableFactory factory()
 *
 * @mixin UserTaggable
 */
class UserTaggable extends Pivot
{
    const POLYMORPHIC_NAME = 'taggable';
    /* |--- GLOBAL VARIABLES ---| */
    public $timestamps = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
