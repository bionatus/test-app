<?php

namespace App\Models;

use Database\Factories\AgentCallFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static AgentCallFactory factory()
 *
 * @mixin AgentCall
 */
class AgentCall extends Pivot
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RINGING     = 'ringing';
    const STATUS_INVALID     = 'invalid';
    const STATUS_DROPPED     = 'dropped';
    const STATUS_COMPLETED   = 'completed';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'       => 'integer',
        'agent_id' => 'integer',
        'call_id'  => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function isRinging(): bool
    {
        return self::STATUS_RINGING === $this->status;
    }

    public function isInProgress(): bool
    {
        return self::STATUS_IN_PROGRESS === $this->status;
    }

    public function isCompleted(): bool
    {
        return self::STATUS_COMPLETED === $this->status;
    }

    /* |--- RELATIONS ---| */

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
