<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\AirFilter;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AirFilter $resource
 */
class AirFilterResource extends JsonResource implements HasJsonSchema
{
    public function __construct(AirFilter $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'media_type'         => $this->resource->media_type,
            'merv_rating'        => $this->resource->merv_rating,
            'nominal_width'      => $this->resource->nominal_width,
            'nominal_length'     => $this->resource->nominal_length,
            'nominal_depth'      => $this->resource->nominal_depth,
            'actual_width'       => $this->resource->actual_width,
            'actual_length'      => $this->resource->actual_length,
            'actual_depth'       => $this->resource->actual_depth,
            'efficiency'         => $this->resource->efficiency,
            'max_operating_temp' => $this->resource->max_operating_temp,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'media_type'         => ['type' => ['string', 'null']],
                'merv_rating'        => ['type' => ['integer', 'null']],
                'nominal_width'      => ['type' => ['string', 'null']],
                'nominal_length'     => ['type' => ['string', 'null']],
                'nominal_depth'      => ['type' => ['string', 'null']],
                'actual_width'       => ['type' => ['string', 'null']],
                'actual_length'      => ['type' => ['string', 'null']],
                'actual_depth'       => ['type' => ['string', 'null']],
                'efficiency'         => ['type' => ['string', 'null']],
                'max_operating_temp' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'media_type',
                'merv_rating',
                'nominal_width',
                'nominal_length',
                'nominal_depth',
                'actual_width',
                'actual_length',
                'actual_depth',
                'efficiency',
                'max_operating_temp',
            ],
            'additionalProperties' => false,
        ];
    }
}
