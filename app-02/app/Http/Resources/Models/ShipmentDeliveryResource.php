<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\ShipmentDelivery;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ShipmentDelivery $resource
 */
class ShipmentDeliveryResource extends JsonResource implements HasJsonSchema
{
    public function __construct(ShipmentDelivery $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $shipmentDelivery           = $this->resource;
        $destination                = $shipmentDelivery->destinationAddress;
        $shipmentDeliveryPreference = $shipmentDelivery->shipmentDeliveryPreference;

        return [
            'destination_address'          => ($destination) ? new AddressResource($destination) : null,
            'shipment_delivery_preference' => ($shipmentDeliveryPreference) ?
                new ShipmentDeliveryPreferenceResource($shipmentDeliveryPreference) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'destination_address'          => ['type' => ['object', 'null']],
                'shipment_delivery_preference' => ['type' => ['object', 'null']],
            ],
            'required'             => [
                'destination_address',
                'shipment_delivery_preference',
            ],
            'additionalProperties' => false,
        ];
    }
}
