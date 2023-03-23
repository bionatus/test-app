<?php

namespace App\Models;

use App;
use Database\Factories\PubnubChannelFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static PubnubChannelFactory factory()
 *
 * @mixin PubnubChannel
 */
class PubnubChannel extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'          => 'integer',
        'user_id'     => 'integer',
        'supplier_id' => 'integer',
        'channel'     => 'string',
    ];
    protected $table = 'pubnub_channels';

    /* |--- FUNCTIONS ---| */

    public function getRouteKeyName(): string
    {
        return 'channel';
    }

    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
