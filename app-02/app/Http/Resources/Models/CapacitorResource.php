<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Capacitor;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Capacitor $resource
 */
class CapacitorResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Capacitor $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'microfarads'            => $this->resource->microfarads,
            'voltage'                => $this->resource->voltage,
            'shape'                  => $this->resource->shape,
            'tolerance'              => $this->resource->tolerance,
            'operating_temperature'  => $this->resource->operating_temperature,
            'depth'                  => $this->resource->depth,
            'height'                 => $this->resource->height,
            'width'                  => $this->resource->width,
            'part_number_correction' => $this->resource->part_number_correction,
            'notes'                  => $this->resource->notes,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'microfarads'            => ['type' => ['string', 'null']],
                'voltage'                => ['type' => ['string', 'null']],
                'shape'                  => ['type' => ['string', 'null']],
                'tolerance'              => ['type' => ['string', 'null']],
                'operating_temperature'  => ['type' => ['string', 'null']],
                'depth'                  => ['type' => ['string', 'null']],
                'height'                 => ['type' => ['string', 'null']],
                'width'                  => ['type' => ['string', 'null']],
                'part_number_correction' => ['type' => ['string', 'null']],
                'notes'                  => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'microfarads',
                'voltage',
                'shape',
                'tolerance',
                'operating_temperature',
                'depth',
                'height',
                'width',
                'part_number_correction',
                'notes',
            ],
            'additionalProperties' => false,
        ];
    }
}
