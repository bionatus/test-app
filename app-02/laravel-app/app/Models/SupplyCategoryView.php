<?php

namespace App\Models;

use Database\Factories\SupplyCategoryViewFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SupplyCategoryViewFactory factory()
 *
 * @mixin SupplyCategoryView
 */
class SupplyCategoryView extends Model
{
    /* |--- CONSTANTS ---| */
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'user_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplyCategory(): BelongsTo
    {
        return $this->belongsTo(SupplyCategory::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
