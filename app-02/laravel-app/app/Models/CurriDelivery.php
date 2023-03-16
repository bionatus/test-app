<?php

namespace App\Models;

use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;
use Config;
use Database\Factories\CurriDeliveryFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static CurriDeliveryFactory factory()
 *
 * @mixin CurriDelivery
 */
class CurriDelivery extends Model implements IsDeliverable
{
    use IsOrderDelivery;

    const MORPH_ALIAS                             = 'curri_delivery';
    const VEHICLE_TYPE_CAR                        = 'car';
    const VEHICLE_TYPE_RACK_TRUCK                 = 'truck-with-pipe-rack';
    const VEHICLE_TYPES_ALL                       = [
        self::VEHICLE_TYPE_CAR,
        self::VEHICLE_TYPE_RACK_TRUCK,
    ];
    const DELIVERY_STATUS_PENDING                 = 'pending';
    const DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN      = 'en_route_to_origin';
    const DELIVERY_STATUS_AT_ORIGIN               = 'at_origin';
    const DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION = 'en_route_to_destination';
    const DELIVERY_STATUS_AT_DESTINATION          = 'at_destination';
    const DELIVERY_STATUS_DELIVERED               = 'delivered';
    const DELIVERY_STATUSES_ON_ROUTE              = [
        self::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN,
        self::DELIVERY_STATUS_AT_ORIGIN,
        self::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION,
        self::DELIVERY_STATUS_AT_DESTINATION,
    ];
    const DELIVERY_FINISHED_STATUSES              = [
        self::DELIVERY_STATUS_AT_DESTINATION,
        self::DELIVERY_STATUS_DELIVERED,
    ];
    /* |--- GLOBAL VARIABLES ---| */
    public    $incrementing = false;
    protected $casts        = [
        'id'                     => 'integer',
        'origin_address_id'      => 'integer',
        'destination_address_id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public static function usesDestinationAddress(): bool
    {
        return true;
    }

    public static function usesOriginAddress(): bool
    {
        return true;
    }

    public function hasDestinationAddress(): bool
    {
        return !!$this->destination_address_id;
    }

    public function hasOriginAddress(): bool
    {
        return !!$this->origin_address_id;
    }

    public function isConfirmedBySupplier(): bool
    {
        return !!$this->supplier_confirmed_at;
    }

    public function isBooked(): bool
    {
        return !!$this->book_id;
    }

    public function isConfirmedByUser(): bool
    {
        return !!$this->user_confirmed_at;
    }

    public function createSubstatusHandler(Order $order): OrderSubstatusUpdated
    {
        return $order->createSubstatusCurriHandler();
    }

    /* |--- RELATIONS ---| */
    public function originAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'origin_address_id');
    }

    public function destinationAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'destination_address_id');
    }

    /* |--- ACCESSORS ---| */
    public function getTrackingUrlAttribute(): ?string
    {
        $prefixTrackingUrl = Config::get('curri.prefix_tracking_url');
        $trackingId        = $this->tracking_id;

        return $trackingId ? $prefixTrackingUrl . $trackingId : null;
    }
    /* |--- MUTATORS ---| */
}
