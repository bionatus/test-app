<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\OtherDelivery;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property OtherDelivery $resource
 */
class OtherDeliveryResource extends JsonResource implements HasJsonSchema
{
    public function __construct(OtherDelivery $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $destination = $this->resource->destinationAddress;

        return [
            'destination_address' => ($destination) ? new AddressResource($destination) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'destination_address' => ['type' => ['object', 'null']],
            ],
            'required'             => [
                'destination_address',
            ],
            'additionalProperties' => false,
        ];
    }
}
