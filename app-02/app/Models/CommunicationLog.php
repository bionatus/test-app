<?php

namespace App\Models;

use Database\Factories\CommunicationLogFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static CommunicationLogFactory factory()
 *
 * @mixin CommunicationLog
 */
class CommunicationLog extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'               => 'integer',
        'communication_id' => 'integer',
        'request'          => 'json',
        'errors'           => 'json',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function communication(): BelongsTo
    {
        return $this->belongsTo(Communication::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
