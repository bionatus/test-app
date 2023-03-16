<?php

namespace App\Models;

use Database\Factories\PartSearchCounterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static PartSearchCounterFactory factory()
 *
 * @mixin PartSearchCounter
 */
class PartSearchCounter extends Model
{
    use HasUuid;
    use HasFactory;

    /* |--- GLOBAL VARIABLES ---| */
    public    $table = 'part_search_counter';
    protected $casts = [
        'id'       => 'integer',
        'staff_id' => 'integer',
        'user_id'  => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partDetailCounters(): hasMany
    {
        return $this->hasMany(PartDetailCounter::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
