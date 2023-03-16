<?php

namespace App\Models;

use Database\Factories\SeriesUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SeriesUserFactory factory()
 *
 * @mixin SeriesUser
 */
class SeriesUser extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'        => 'integer',
        'user_id'   => 'integer',
        'series_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
