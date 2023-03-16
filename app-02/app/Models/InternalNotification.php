<?php

namespace App\Models;

use Database\Factories\InternalNotificationFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @method static InternalNotificationFactory factory()
 *
 * @mixin InternalNotification
 */
class InternalNotification extends Model
{
    use HasUuid;

    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'      => 'integer',
        'user_id' => 'integer',
        'uuid'    => 'string',
        'read_at' => 'datetime',
        'data'    => 'array',
    ];

    /* |--- FUNCTIONS ---| */

    public function isRead(): bool
    {
        return !!$this->read_at;
    }

    public function read(): self
    {
        $this->read_at = Carbon::now();
        $this->save();

        return $this;
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->getKey();
    }

    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
