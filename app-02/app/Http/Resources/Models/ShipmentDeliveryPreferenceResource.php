<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\ShipmentDeliveryPreference;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ShipmentDeliveryPreference $resource
 */
class ShipmentDeliveryPreferenceResource extends JsonResource implements HasJsonSchema
{
    public function __construct(ShipmentDeliveryPreference $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id' => $this->resource->getRouteKey(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
            ],
            'additionalProperties' => false,
        ];
    }
}
