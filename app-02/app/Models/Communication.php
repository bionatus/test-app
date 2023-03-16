<?php

namespace App\Models;

use Database\Factories\CommunicationFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static CommunicationFactory factory()
 *
 * @mixin Communication
 */
class Communication extends Model
{
    use HasUuid;

    const PROVIDER_TWILIO = 'twilio';
    const CHANNEL_CALL    = 'call';
    const CHANNEL_CHAT    = 'chat';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'         => 'integer',
        'session_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function isCall(): bool
    {
        if (self::CHANNEL_CALL !== $this->channel) {
            return false;
        }

        return !!$this->call;
    }

    /* |--- RELATIONS ---| */

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function call(): HasOne
    {
        return $this->hasOne(Call::class, Call::keyName());
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function agentCalls(): HasManyThrough
    {
        return $this->hasManyThrough(AgentCall::class, Call::class, 'id', 'call_id');
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
