<?php

namespace App\Models;

use Database\Factories\SessionFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @method static SessionFactory factory()
 *
 * @mixin Session
 */
class Session extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'subject_id' => 'integer',
        'ticket_id'  => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    public function communicationLogs(): HasManyThrough
    {
        return $this->hasManyThrough(CommunicationLog::class, Communication::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
