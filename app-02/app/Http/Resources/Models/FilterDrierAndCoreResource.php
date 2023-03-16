<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\FilterDrierAndCore;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property FilterDrierAndCore $resource
 */
class FilterDrierAndCoreResource extends JsonResource implements HasJsonSchema
{
    public function __construct(FilterDrierAndCore $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'volume'                 => $this->resource->volume,
            'inlet_diameter'         => $this->resource->inlet_diameter,
            'inlet_connection_type'  => $this->resource->inlet_connection_type,
            'outlet_diameter'        => $this->resource->outlet_diameter,
            'outlet_connection_type' => $this->resource->outlet_connection_type,
            'direction_of_flow'      => $this->resource->direction_of_flow,
            'desiccant_type'         => $this->resource->desiccant_type,
            'number_of_cores'        => $this->resource->number_of_cores,
            'options'                => $this->resource->options,
            'rated_capacity'         => $this->resource->rated_capacity,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'volume'                 => ['type' => ['integer', 'null']],
                'inlet_diameter'         => ['type' => ['string', 'null']],
                'inlet_connection_type'  => ['type' => ['string', 'null']],
                'outlet_diameter'        => ['type' => ['string', 'null']],
                'outlet_connection_type' => ['type' => ['string', 'null']],
                'direction_of_flow'      => ['type' => ['string', 'null']],
                'desiccant_type'         => ['type' => ['string', 'null']],
                'number_of_cores'        => ['type' => ['integer', 'null']],
                'options'                => ['type' => ['string', 'null']],
                'rated_capacity'         => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'volume',
                'inlet_diameter',
                'inlet_connection_type',
                'outlet_diameter',
                'outlet_connection_type',
                'direction_of_flow',
                'desiccant_type',
                'number_of_cores',
                'options',
                'rated_capacity',
            ],
            'additionalProperties' => false,
        ];
    }
}
