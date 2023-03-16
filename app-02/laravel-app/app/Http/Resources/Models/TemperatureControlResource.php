<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\TemperatureControl;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property TemperatureControl $resource
 */
class TemperatureControlResource extends JsonResource implements HasJsonSchema
{
    public function __construct(TemperatureControl $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'programmable'          => $this->resource->programmable,
            'application'           => $this->resource->application,
            'wifi'                  => $this->resource->wifi,
            'power_requirements'    => $this->resource->power_requirements,
            'operating_voltage'     => $this->resource->operating_voltage,
            'switch'                => $this->resource->switch,
            'action'                => $this->resource->action,
            'operation_of_contacts' => $this->resource->operation_of_contacts,
            'adjustable'            => $this->resource->adjustable,
            'range_minimum'         => $this->resource->range_minimum,
            'range_maximum'         => $this->resource->range_maximum,
            'reset_minimum'         => $this->resource->reset_minimum,
            'reset_maximum'         => $this->resource->reset_maximum,
            'differential_minimum'  => $this->resource->differential_minimum,
            'differential_maximum'  => $this->resource->differential_maximum,
            'setpoint'              => $this->resource->setpoint,
            'reset'                 => $this->resource->reset,
            'reset_type'            => $this->resource->reset_type,
            'capillary_length'      => $this->resource->capillary_length,
            'max_amps'              => $this->resource->max_amps,
            'max_volts'             => $this->resource->max_volts,
            'replaceable_bulb'      => $this->resource->replaceable_bulb,
            'mount'                 => $this->resource->mount,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'programmable'          => ['type' => ['string', 'null']],
                'application'           => ['type' => ['string', 'null']],
                'wifi'                  => ['type' => ['boolean', 'null']],
                'power_requirements'    => ['type' => ['string', 'null']],
                'operating_voltage'     => ['type' => ['string', 'null']],
                'switch'                => ['type' => ['string', 'null']],
                'action'                => ['type' => ['string', 'null']],
                'operation_of_contacts' => ['type' => ['string', 'null']],
                'adjustable'            => ['type' => ['boolean', 'null']],
                'range_minimum'         => ['type' => ['string', 'null']],
                'range_maximum'         => ['type' => ['string', 'null']],
                'reset_minimum'         => ['type' => ['integer', 'null']],
                'reset_maximum'         => ['type' => ['integer', 'null']],
                'differential_minimum'  => ['type' => ['string', 'null']],
                'differential_maximum'  => ['type' => ['string', 'null']],
                'setpoint'              => ['type' => ['string', 'null']],
                'reset'                 => ['type' => ['string', 'null']],
                'reset_type'            => ['type' => ['string', 'null']],
                'capillary_length'      => ['type' => ['number', 'null']],
                'max_amps'              => ['type' => ['string', 'null']],
                'max_volts'             => ['type' => ['string', 'null']],
                'replaceable_bulb'      => ['type' => ['boolean', 'null']],
                'mount'                 => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'programmable',
                'application',
                'wifi',
                'power_requirements',
                'operating_voltage',
                'switch',
                'action',
                'operation_of_contacts',
                'adjustable',
                'range_minimum',
                'range_maximum',
                'reset_minimum',
                'reset_maximum',
                'differential_minimum',
                'differential_maximum',
                'setpoint',
                'reset',
                'reset_type',
                'capillary_length',
                'max_amps',
                'max_volts',
                'replaceable_bulb',
                'mount',
            ],
            'additionalProperties' => false,
        ];
    }
}
