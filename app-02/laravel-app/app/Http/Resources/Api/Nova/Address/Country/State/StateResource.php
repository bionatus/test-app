<?php

namespace App\Http\Resources\Api\Nova\Address\Country\State;

use App\Http\Resources\HasJsonSchema;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\State;

/**
 * @property State $resource
 */
class StateResource extends JsonResource implements HasJsonSchema
{
    public function __construct(State $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'value'   => $this->resource->isoCode,
            'display' => $this->resource->getName(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'value'   => ['type' => ['string']],
                'display' => ['type' => ['string']],
            ],
            'required'             => [
                'value',
                'display',
            ],
            'additionalProperties' => false,
        ];
    }
}
