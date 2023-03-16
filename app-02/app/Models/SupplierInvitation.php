<?php

namespace App\Models;

use Database\Factories\SupplierInvitationFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SupplierInvitationFactory factory()
 *
 * @mixin SupplierInvitation
 */
class SupplierInvitation extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $table = 'supplier_invitations';
    protected $casts = [
        'id'          => 'integer',
        'supplier_id' => 'integer',
        'user_id'     => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
