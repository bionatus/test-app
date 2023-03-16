<?php

namespace App\Models;

use Database\Factories\PendingApprovalViewFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static PendingApprovalViewFactory factory()
 *
 * @mixin PendingApprovalView
 */
class PendingApprovalView extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
