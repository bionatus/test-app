<?php

namespace App\Models;

use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;
use Database\Factories\ShipmentDeliveryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static ShipmentDeliveryFactory factory()
 *
 * @mixin ShipmentDelivery
 */
class ShipmentDelivery extends Model implements IsDeliverable
{
    use IsOrderDelivery;

    const MORPH_ALIAS = 'shipment_delivery';
    /* |--- GLOBAL VARIABLES ---| */
    public    $incrementing = false;
    protected $casts        = [
        'id'                              => 'integer',
        'destination_address_id'          => 'integer',
        'shipment_delivery_preference_id' => 'integer',
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
        return $this->belongsTo(Address::class);
    }

    public function shipmentDeliveryPreference(): BelongsTo
    {
        return $this->belongsTo(ShipmentDeliveryPreference::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
