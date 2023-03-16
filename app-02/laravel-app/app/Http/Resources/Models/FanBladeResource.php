<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\FanBlade;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property FanBlade $resource
 */
class FanBladeResource extends JsonResource implements HasJsonSchema
{
    public function __construct(FanBlade $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'diameter'         => $this->resource->diameter,
            'number_of_blades' => $this->resource->number_of_blades,
            'pitch'            => $this->resource->pitch,
            'bore'             => $this->resource->bore,
            'rotation'         => $this->resource->rotation,
            'rpm'              => $this->resource->rpm,
            'cfm'              => $this->resource->cfm,
            'bhp'              => $this->resource->bhp,
            'material'         => $this->resource->material,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'diameter'         => ['type' => ['string', 'null']],
                'number_of_blades' => ['type' => ['integer', 'null']],
                'pitch'            => ['type' => ['string', 'null']],
                'bore'             => ['type' => ['string', 'null']],
                'rotation'         => ['type' => ['string', 'null']],
                'rpm'              => ['type' => ['integer', 'null']],
                'cfm'              => ['type' => ['string', 'null']],
                'bhp'              => ['type' => ['string', 'null']],
                'material'         => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'diameter',
                'number_of_blades',
                'pitch',
                'bore',
                'rotation',
                'rpm',
                'cfm',
                'bhp',
                'material',
            ],
            'additionalProperties' => false,
        ];
    }
}
