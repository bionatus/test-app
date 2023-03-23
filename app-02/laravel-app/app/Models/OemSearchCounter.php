<?php

namespace App\Models;

use Database\Factories\OemSearchCounterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static OemSearchCounterFactory factory()
 *
 * @mixin OemSearchCounter
 */
class OemSearchCounter extends Model
{
    use HasUuid;
    use HasFactory;

    /* |--- GLOBAL VARIABLES ---| */
    public    $table = 'oem_search_counter';
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

    public function oemDetailCounters(): hasMany
    {
        return $this->hasMany(OemDetailCounter::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
