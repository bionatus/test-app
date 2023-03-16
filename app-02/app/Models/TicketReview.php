<?php

namespace App\Models;

use Database\Factories\TicketReviewFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static TicketReviewFactory factory()
 *
 * @mixin TicketReview
 */
class TicketReview extends Model
{
    use HasUuid;

    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'        => 'integer',
        'ticket_id' => 'integer',
        'agent_id'  => 'integer',
        'rating'    => 'integer',
        'closed_at' => 'datetime',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
