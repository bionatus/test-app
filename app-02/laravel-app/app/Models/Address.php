<?php

namespace App\Models;

use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static AddressFactory factory()
 *
 * @mixin Address
 */
class Address extends Model
{
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id' => 'integer',
    ];

    /* |--- RELATIONS ---| */
    public function otherDelivery(): HasOne
    {
        return $this->hasOne(OtherDelivery::class, 'destination_address_id');
    }

    public function warehouseDelivery(): HasOne
    {
        return $this->hasOne(WarehouseDelivery::class, 'destination_address_id');
    }

    public function shipmentDelivery(): HasOne
    {
        return $this->hasOne(ShipmentDelivery::class, 'destination_address_id');
    }

    public function originCurriDelivery(): HasOne
    {
        return $this->hasOne(CurriDelivery::class, 'origin_address_id');
    }

    public function destinationCurriDelivery(): HasOne
    {
        return $this->hasOne(CurriDelivery::class, 'destination_address_id');
    }
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
