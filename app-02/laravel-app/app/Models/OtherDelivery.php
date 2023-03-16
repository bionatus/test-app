<?php

namespace App\Models;

use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;
use Database\Factories\OtherDeliveryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static OtherDeliveryFactory factory()
 *
 * @mixin OtherDelivery
 */
class OtherDelivery extends Model implements IsDeliverable
{
    use IsOrderDelivery;

    const MORPH_ALIAS = 'other_delivery';
    /* |--- GLOBAL VARIABLES ---| */
    public    $incrementing = false;
    protected $casts        = [
        'id'                     => 'integer',
        'destination_address_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public static function usesDestinationAddress(): bool
    {
        return true;
    }

    public static function usesOriginAddress(): bool
    {
        return false;
    }

    public function hasDestinationAddress(): bool
    {
        return !!$this->destination_address_id;
    }

    public function hasOriginAddress(): bool
    {
        return false;
    }

    public function createSubstatusHandler(Order $order): OrderSubstatusUpdated
    {
        return $order->createSubstatusShipmentHandler();
    }

    /* |--- RELATIONS ---| */

    public function destinationAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'destination_address_id');
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
