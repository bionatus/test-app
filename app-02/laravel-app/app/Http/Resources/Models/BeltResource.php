<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Belt;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Belt $resource
 */
class BeltResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Belt $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'family'      => $this->resource->family,
            'belt_type'   => $this->resource->belt_type,
            'belt_length' => $this->resource->belt_length,
            'pitch'       => $this->resource->pitch,
            'thickness'   => $this->resource->thickness,
            'top_width'   => $this->resource->top_width,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'family'      => ['type' => ['string', 'null']],
                'belt_type'   => ['type' => ['string', 'null']],
                'belt_length' => ['type' => ['string', 'null']],
                'pitch'       => ['type' => ['string', 'null']],
                'thickness'   => ['type' => ['string', 'null']],
                'top_width'   => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'family',
                'belt_type',
                'belt_length',
                'pitch',
                'thickness',
                'top_width',
            ],
            'additionalProperties' => false,
        ];
    }
}
