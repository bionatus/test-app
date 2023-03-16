<?php

namespace App\Models;

use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;
use Database\Factories\PickupFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static PickupFactory factory()
 *
 * @mixin Pickup
 */
class Pickup extends Model implements IsDeliverable
{
    use IsOrderDelivery;

    const MORPH_ALIAS = 'pickup';
    /* |--- GLOBAL VARIABLES ---| */
    public    $incrementing = false;
    protected $casts        = [
        'id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public static function usesDestinationAddress(): bool
    {
        return false;
    }

    public static function usesOriginAddress(): bool
    {
        return false;
    }

    public function hasDestinationAddress(): bool
    {
        return false;
    }

    public function hasOriginAddress(): bool
    {
        return false;
    }

    public function createSubstatusHandler(Order $order): OrderSubstatusUpdated
    {
        return $order->createSubstatusPickupHandler();
    }

    /* |--- RELATIONS ---| */
    public function destinationAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'destination_address_id');
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
