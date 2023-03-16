<?php

namespace App\Http\Resources\Types;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\Country;

/**
 * @property Country $resource
 */
class CountryResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Country $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'code' => $this->resource->getCode(),
            'name' => $this->resource->getName(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'code' => ['type' => ['string']],
                'name' => ['type' => ['string']],
            ],
            'required'             => [
                'code',
                'name',
            ],
            'additionalProperties' => false,
        ];
    }
}
