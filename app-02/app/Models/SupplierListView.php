<?php

namespace App\Models;

use Database\Factories\SupplierListViewFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SupplierListViewFactory factory()
 *
 * @mixin SupplierUser
 */
class SupplierListView extends Model
{
    /* |--- CONSTANTS ---| */
    /* |--- GLOBAL VARIABLES ---| */

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
