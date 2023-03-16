<?php

namespace App\Models;

use App\Models\AgentCall\Scopes\InProgress;
use App\Models\AgentCall\Scopes\Ringing;
use Database\Factories\CallFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static CallFactory factory()
 *
 * @mixin Call
 */
class Call extends Model
{
    const STATUS_IN_PROGRESS                = 'in_progress';
    const STATUS_COMPLETED                  = 'completed';
    const STATUS_INVALID                    = 'invalid';
    const TWILIO_CALL_STATUS_QUEUED         = 'queued';
    const TWILIO_CALL_STATUS_INITIATED      = 'initiated';
    const TWILIO_CALL_STATUS_RINGING        = 'ringing';
    const TWILIO_CALL_STATUS_IN_PROGRESS    = 'in-progress';
    const TWILIO_CALL_STATUS_COMPLETED      = 'completed';
    const TWILIO_CALL_STATUS_BUSY           = 'busy';
    const TWILIO_CALL_STATUS_FAILED         = 'failed';
    const TWILIO_CALL_STATUS_NO_ANSWER      = 'no-answer';
    const TWILIO_CALL_STATUS_CANCELED       = 'canceled';
    const TWILIO_DIAL_CALL_STATUS_COMPLETED = 'completed';
    const TWILIO_DIAL_CALL_STATUS_ANSWERED  = 'answered';
    const TWILIO_DIAL_CALL_STATUS_BUSY      = 'busy';
    const TWILIO_DIAL_CALL_STATUS_NO_ANSWER = 'no-answer';
    const TWILIO_DIAL_CALL_STATUS_FAILED    = 'failed';
    const TWILIO_DIAL_CALL_STATUS_CANCELED  = 'canceled';
    const TWILIO_STATUS_CALLBACK_RINGING    = 'ringing';
    const TWILIO_STATUS_CALLBACK_ANSWERED   = 'answered';
    const TWILIO_STATUS_CALLBACK_COMPLETED  = 'completed';
    /* |--- GLOBAL VARIABLES ---| */

    public    $incrementing = false;
    protected $casts        = [
        'id'       => 'integer',
        'user_id'  => 'integer',
        'payloads' => 'json',
    ];

    /* |--- FUNCTIONS ---| */

    public function complete(): self
    {
        $this->status = Call::STATUS_COMPLETED;
        $this->save();
        $this->freeAgents();

        return $this;
    }

    public function freeAgents(): self
    {
        $this->agentCalls()->scoped(new Ringing())->update([
            'status' => AgentCall::STATUS_DROPPED,
        ]);

        $this->agentCalls()->scoped(new InProgress())->update([
            'status' => AgentCall::STATUS_COMPLETED,
        ]);

        return $this;
    }

    /* |--- RELATIONS ---| */

    public function communication(): BelongsTo
    {
        return $this->belongsTo(Communication::class, self::keyName());
    }

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class);
    }

    public function agentCalls(): HasMany
    {
        return $this->hasMany(AgentCall::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
