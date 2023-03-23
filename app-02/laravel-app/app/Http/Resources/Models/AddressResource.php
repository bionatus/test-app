<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Address;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Address $resource
 */
class AddressResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Address $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'address_1' => $this->resource->address_1,
            'address_2' => $this->resource->address_2,
            'city'      => $this->resource->city,
            'state'     => $this->resource->state,
            'country'   => $this->resource->country,
            'zip_code'  => $this->resource->zip_code,
            'latitude'  => $this->resource->latitude,
            'longitude' => $this->resource->longitude,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'address_1' => ['type' => ['string']],
                'address_2' => ['type' => ['string', 'null']],
                'city'      => ['type' => ['string', 'null']],
                'state'     => ['type' => ['string', 'null']],
                'country'   => ['type' => ['string', 'null']],
                'zip_code'  => ['type' => ['string', 'null']],
                'latitude'  => ['type' => ['string', 'null']],
                'longitude' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'address_1',
                'address_2',
                'city',
                'state',
                'country',
                'zip_code',
                'latitude',
                'longitude',
            ],
            'additionalProperties' => false,
        ];
    }
}
