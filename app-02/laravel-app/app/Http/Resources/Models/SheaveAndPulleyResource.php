<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\SheaveAndPulley;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SheaveAndPulley $resource
 */
class SheaveAndPulleyResource extends JsonResource implements HasJsonSchema
{
    public function __construct(SheaveAndPulley $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'belt_type'          => $this->resource->belt_type,
            'number_of_grooves'  => $this->resource->number_of_grooves,
            'bore_diameter'      => $this->resource->bore_diameter,
            'outside_diameter'   => $this->resource->outside_diameter,
            'adjustable'         => $this->resource->adjustable,
            'bore_mate_type'     => $this->resource->bore_mate_type,
            'bushing_connection' => $this->resource->bushing_connection,
            'keyway_types'       => $this->resource->keyway_types,
            'keyway_height'      => $this->resource->keyway_height,
            'keyway_width'       => $this->resource->keyway_width,
            'minimum_dd'         => $this->resource->minimum_dd,
            'maximum_dd'         => $this->resource->maximum_dd,
            'material'           => $this->resource->material,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'belt_type'          => ['type' => ['string', 'null']],
                'number_of_grooves'  => ['type' => ['integer', 'null']],
                'bore_diameter'      => ['type' => ['string', 'null']],
                'outside_diameter'   => ['type' => ['number', 'null']],
                'adjustable'         => ['type' => ['boolean', 'null']],
                'bore_mate_type'     => ['type' => ['string', 'null']],
                'bushing_connection' => ['type' => ['string', 'null']],
                'keyway_types'       => ['type' => ['string', 'null']],
                'keyway_height'      => ['type' => ['string', 'null']],
                'keyway_width'       => ['type' => ['string', 'null']],
                'minimum_dd'         => ['type' => ['number', 'null']],
                'maximum_dd'         => ['type' => ['number', 'null']],
                'material'           => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'belt_type',
                'number_of_grooves',
                'bore_diameter',
                'outside_diameter',
                'adjustable',
                'bore_mate_type',
                'bushing_connection',
                'keyway_types',
                'keyway_height',
                'keyway_width',
                'minimum_dd',
                'maximum_dd',
                'material',
            ],
            'additionalProperties' => false,
        ];
    }
}
