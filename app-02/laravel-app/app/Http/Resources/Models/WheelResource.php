<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Wheel;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Wheel $resource
 */
class WheelResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Wheel $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'diameter'         => $this->resource->diameter,
            'width'            => $this->resource->width,
            'bore'             => $this->resource->bore,
            'rotation'         => $this->resource->rotation,
            'max_rpm'          => $this->resource->max_rpm,
            'material'         => $this->resource->material,
            'keyway'           => $this->resource->keyway,
            'center_disc'      => $this->resource->center_disc,
            'number_hubs'      => $this->resource->number_hubs,
            'hub_lock'         => $this->resource->hub_lock,
            'number_setscrews' => $this->resource->number_setscrews,
            'number_blades'    => $this->resource->number_blades,
            'wheel_type'       => $this->resource->wheel_type,
            'drive_type'       => $this->resource->drive_type,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'diameter'         => ['type' => ['string', 'null']],
                'width'            => ['type' => ['string', 'null']],
                'bore'             => ['type' => ['string', 'null']],
                'rotation'         => ['type' => ['string', 'null']],
                'max_rpm'          => ['type' => ['integer', 'null']],
                'material'         => ['type' => ['string', 'null']],
                'keyway'           => ['type' => ['string', 'null']],
                'center_disc'      => ['type' => ['string', 'null']],
                'number_hubs'      => ['type' => ['integer', 'null']],
                'hub_lock'         => ['type' => ['string', 'null']],
                'number_setscrews' => ['type' => ['string', 'null']],
                'number_blades'    => ['type' => ['integer', 'null']],
                'wheel_type'       => ['type' => ['string', 'null']],
                'drive_type'       => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'diameter',
                'width',
                'bore',
                'rotation',
                'max_rpm',
                'material',
                'keyway',
                'center_disc',
                'number_hubs',
                'hub_lock',
                'number_setscrews',
                'number_blades',
                'wheel_type',
                'drive_type',
            ],
            'additionalProperties' => false,
        ];
    }
}
