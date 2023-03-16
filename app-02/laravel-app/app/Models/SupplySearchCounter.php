<?php

namespace App\Models;

use Database\Factories\SupplySearchCounterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SupplySearchCounterFactory factory()
 *
 * @mixin SupplySearchCounter
 */
class SupplySearchCounter extends Model
{
    use HasUuid;
    use HasFactory;

    /* |--- GLOBAL VARIABLES ---| */
    public    $table = 'supply_search_counter';
    protected $casts = [
        'id'      => 'integer',
        'user_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
