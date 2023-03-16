<?php

namespace App\Models;

use Database\Factories\PartDetailCounterFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static PartDetailCounterFactory factory()
 *
 * @mixin PartDetailCounter
 */
class PartDetailCounter extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    public    $table = 'part_detail_counter';
    protected $casts = [
        'id'                     => 'integer',
        'part_id'                => 'integer',
        'staff_id'               => 'integer',
        'user_id'                => 'integer',
        'part_search_counter_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partSearchCounter(): BelongsTo
    {
        return $this->belongsTo(PartSearchCounter::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
