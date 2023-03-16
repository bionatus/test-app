<?php

namespace App\Models;

use Database\Factories\TermUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static TermUserFactory factory()
 *
 * @mixin TermUser
 */
class TermUser extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'      => 'integer',
        'term_id' => 'integer',
        'user_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
