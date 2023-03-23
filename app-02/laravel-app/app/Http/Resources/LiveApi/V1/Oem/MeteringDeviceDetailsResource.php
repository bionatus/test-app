<?php

namespace App\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class MeteringDeviceDetailsResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'device_type'         => $this->resource->device_type,
            'devices_per_circuit' => $this->resource->devices_per_circuit,
            'total_devices'       => $this->resource->total_devices,
            'device_size'         => $this->resource->device_size,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'array'],
            'properties'           => [
                'device_type'         => ['type' => ['string', 'null']],
                'devices_per_circuit' => ['type' => ['integer', 'null']],
                'total_devices'       => ['type' => ['integer', 'null']],
                'device_size'         => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'device_type',
                'devices_per_circuit',
                'total_devices',
                'device_size',
            ],
            'additionalProperties' => false,
        ];
    }
}
