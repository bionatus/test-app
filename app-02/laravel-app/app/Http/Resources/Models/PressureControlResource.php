<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\PressureControl;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PressureControl $resource
 */
class PressureControlResource extends JsonResource implements HasJsonSchema
{
    public function __construct(PressureControl $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'setpoint'              => $this->resource->setpoint,
            'reset'                 => $this->resource->reset,
            'range_minimum'         => $this->resource->range_minimum,
            'range_maximum'         => $this->resource->range_maximum,
            'reset_minimum'         => $this->resource->reset_minimum,
            'reset_maximum'         => $this->resource->reset_maximum,
            'differential_minimum'  => $this->resource->differential_minimum,
            'differential_maximum'  => $this->resource->differential_maximum,
            'operation_of_contacts' => $this->resource->operation_of_contacts,
            'switch'                => $this->resource->switch,
            'action'                => $this->resource->action,
            'reset_type'            => $this->resource->reset_type,
            'connection_type'       => $this->resource->connection_type,
            'max_amps'              => $this->resource->max_amps,
            'max_volts'             => $this->resource->max_volts,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'setpoint'              => ['type' => ['string', 'null']],
                'reset'                 => ['type' => ['string', 'null']],
                'range_minimum'         => ['type' => ['integer', 'null']],
                'range_maximum'         => ['type' => ['integer', 'null']],
                'reset_minimum'         => ['type' => ['integer', 'null']],
                'reset_maximum'         => ['type' => ['integer', 'null']],
                'differential_minimum'  => ['type' => ['string', 'null']],
                'differential_maximum'  => ['type' => ['string', 'null']],
                'operation_of_contacts' => ['type' => ['string', 'null']],
                'switch'                => ['type' => ['string', 'null']],
                'action'                => ['type' => ['string', 'null']],
                'reset_type'            => ['type' => ['string', 'null']],
                'connection_type'       => ['type' => ['string', 'null']],
                'max_amps'              => ['type' => ['string', 'null']],
                'max_volts'             => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'setpoint',
                'reset',
                'range_minimum',
                'range_maximum',
                'reset_minimum',
                'reset_maximum',
                'differential_minimum',
                'differential_maximum',
                'operation_of_contacts',
                'switch',
                'action',
                'reset_type',
                'connection_type',
                'max_amps',
                'max_volts',
            ],
            'additionalProperties' => false,
        ];
    }
}
