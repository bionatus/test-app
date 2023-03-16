<?php

namespace App\Models;

use Database\Factories\ApiUsageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static ApiUsageFactory factory()
 *
 * @mixin ApiUsage
 */
class ApiUsage extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'          => 'integer',
        'user_id'     => 'integer',
        'supplier_id' => 'integer',
        'date'        => 'date',
    ];
    /* |--- FUNCTIONS ---| */
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
