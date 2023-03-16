<?php

namespace App\Models;

use Database\Factories\VideoElapsedTimeFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static VideoElapsedTimeFactory factory()
 *
 * @mixin VideoElapsedTime
 */
class VideoElapsedTime extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
