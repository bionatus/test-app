<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\HardStartKit;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property HardStartKit $resource
 */
class HardStartKitResource extends JsonResource implements HasJsonSchema
{
    public function __construct(HardStartKit $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'operating_voltage' => $this->resource->operating_voltage,
            'max_hp'            => $this->resource->max_hp,
            'min_hp'            => $this->resource->min_hp,
            'max_tons'          => $this->resource->max_tons,
            'min_tons'          => $this->resource->min_tons,
            'max_capacitance'   => $this->resource->max_capacitance,
            'min_capacitance'   => $this->resource->min_capacitance,
            'tolerance'         => $this->resource->tolerance,
            'torque_increase'   => $this->resource->torque_increase,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'operating_voltage' => ['type' => ['string', 'null']],
                'max_hp'            => ['type' => ['string', 'null']],
                'min_hp'            => ['type' => ['string', 'null']],
                'max_tons'          => ['type' => ['string', 'null']],
                'min_tons'          => ['type' => ['string', 'null']],
                'max_capacitance'   => ['type' => ['string', 'null']],
                'min_capacitance'   => ['type' => ['string', 'null']],
                'tolerance'         => ['type' => ['string', 'null']],
                'torque_increase'   => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'operating_voltage',
                'max_hp',
                'min_hp',
                'max_tons',
                'min_tons',
                'max_capacitance',
                'min_capacitance',
                'tolerance',
                'torque_increase',
            ],
            'additionalProperties' => false,
        ];
    }
}
