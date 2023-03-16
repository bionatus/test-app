<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\CrankcaseHeater;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CrankcaseHeater $resource
 */
class CrankcaseHeaterResource extends JsonResource implements HasJsonSchema
{
    public function __construct(CrankcaseHeater $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'watts_power'    => $this->resource->watts_power,
            'voltage'        => $this->resource->voltage,
            'shape'          => $this->resource->shape,
            'min_dimension'  => $this->resource->min_dimension,
            'max_dimension'  => $this->resource->max_dimension,
            'probe_length'   => $this->resource->probe_length,
            'probe_diameter' => $this->resource->probe_diameter,
            'lead_length'    => $this->resource->lead_length,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'watts_power'    => ['type' => ['string', 'null']],
                'voltage'        => ['type' => ['string', 'null']],
                'shape'          => ['type' => ['string', 'null']],
                'min_dimension'  => ['type' => ['string', 'null']],
                'max_dimension'  => ['type' => ['string', 'null']],
                'probe_length'   => ['type' => ['string', 'null']],
                'probe_diameter' => ['type' => ['string', 'null']],
                'lead_length'    => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'watts_power',
                'voltage',
                'shape',
                'min_dimension',
                'max_dimension',
                'probe_length',
                'probe_diameter',
                'lead_length',
            ],
            'additionalProperties' => false,
        ];
    }
}
