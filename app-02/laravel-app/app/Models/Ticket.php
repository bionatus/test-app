<?php

namespace App\Models;

use App\Models\Ticket\Scopes\ByActiveParticipantAgent;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @method static TicketFactory factory()
 *
 * @mixin Ticket
 */
class Ticket extends Model
{
    use HasUuid;

    const MORPH_ALIAS = 'ticket';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'        => 'integer',
        'user_id'   => 'integer',
        'uuid'      => 'string',
        'rating'    => 'integer',
        'closed_at' => 'datetime',
    ];

    /* |--- FUNCTIONS ---| */

    public function isClosed(): bool
    {
        return !!$this->closed_at;
    }

    public function close(): self
    {
        if ($this->isClosed()) {
            return $this;
        }

        $this->closed_at = Carbon::now();
        $this->save();

        return $this;
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->getKey();
    }

    public function isActiveParticipant(Agent $agent): bool
    {
        return !!$this->newQuery()->scoped(new ByActiveParticipantAgent($agent))->count();
    }

    public function isOpen(): bool
    {
        return !$this->isClosed();
    }

    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function communications(): HasManyThrough
    {
        return $this->hasManyThrough(Communication::class, Session::class);
    }

    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, TicketReview::tableName());
    }

    public function ticketReviews(): HasMany
    {
        return $this->hasMany(TicketReview::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
