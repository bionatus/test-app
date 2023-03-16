<?php

namespace App\Models;

use Database\Factories\RelatedActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static RelatedActivityFactory factory()
 *
 * @mixin RelatedActivity
 */
class RelatedActivity extends Model
{
    use HasFactory;

    /* |--- GLOBAL VARIABLES ---| */

    protected $table = 'related_activity_log';
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /* |--- ACCESORS ---| */
    /* |--- MUTATORS ---| */
}
