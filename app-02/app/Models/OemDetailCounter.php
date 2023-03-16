<?php

namespace App\Models;

use Database\Factories\OemDetailCounterFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static OemDetailCounterFactory factory()
 *
 * @mixin OemDetailCounter
 */
class OemDetailCounter extends Pivot
{
    public    $table = 'oem_detail_counter';
    protected $casts = [
        'id'                    => 'integer',
        'oem_id'                => 'integer',
        'staff_id'              => 'integer',
        'user_id'               => 'integer',
        'oem_search_counter_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function oem(): BelongsTo
    {
        return $this->belongsTo(Oem::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function oemSearchCounter(): BelongsTo
    {
        return $this->belongsTo(OemSearchCounter::class);
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
