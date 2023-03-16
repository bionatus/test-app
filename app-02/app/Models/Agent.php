<?php

namespace App\Models;

use App\Models\Scopes\ByRouteKey;
use Database\Factories\AgentFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

/**
 * @method static AgentFactory factory()
 *
 * @mixin Agent
 */
class Agent extends Model
{
    use HasUuid;
    use Notifiable;

    /* |--- GLOBAL VARIABLES ---| */

    public    $timestamps   = false;
    public    $incrementing = false;
    protected $casts        = [
        'id'        => 'integer',
        'uuid'      => 'string',
        'available' => 'boolean',
    ];

    /* |--- FUNCTIONS ---| */

    public function routeNotificationForFcm()
    {
        return $this->user->pushNotificationTokens->pluck('token')->toArray();
    }

    public function setUnavailable(): ?SettingUser
    {
        /** @var Setting $setting */
        $setting = Setting::scoped(new ByRouteKey(Setting::SLUG_AGENT_AVAILABLE))->first();

        if (!$setting) {
            return null;
        }

        return $this->user->setSetting($setting, false);
    }

    /* |--- RELATIONS ---| */

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id');
    }

    public function calls(): BelongsToMany
    {
        return $this->belongsToMany(Call::class);
    }

    public function agentCalls(): HasMany
    {
        return $this->hasMany(AgentCall::class);
    }

    public function reviewedTickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, TicketReview::tableName());
    }

    public function ticketReviews(): HasMany
    {
        return $this->hasMany(TicketReview::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
