<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\GasValve;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property GasValve $resource
 */
class GasValveResource extends JsonResource implements HasJsonSchema
{
    public function __construct(GasValve $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'type_of_gas'            => $this->resource->type_of_gas,
            'stages'                 => $this->resource->stages,
            'capacity'               => $this->resource->capacity,
            'outlet_orientation'     => $this->resource->outlet_orientation,
            'reducer_bushing'        => $this->resource->reducer_bushing,
            'inlet_size'             => $this->resource->inlet_size,
            'outlet_size_type'       => $this->resource->outlet_size_type,
            'pilot_outlet_size'      => $this->resource->pilot_outlet_size,
            'factory_settings'       => $this->resource->factory_settings,
            'max_inlet_pressure'     => $this->resource->max_inlet_pressure,
            'min_adjustable_setting' => $this->resource->min_adjustable_setting,
            'max_adjustable_setting' => $this->resource->max_adjustable_setting,
            'terminal_type'          => $this->resource->terminal_type,
            'electrical_rating'      => $this->resource->electrical_rating,
            'side_outlet_size_type'  => $this->resource->side_outlet_size_type,
            'gas_cock_dial_markings' => $this->resource->gas_cock_dial_markings,
            'ambient_temperature'    => $this->resource->ambient_temperature,
            'amp_rating'             => $this->resource->amp_rating,
            'capillary_length'       => $this->resource->capillary_length,
            'standard_dial'          => $this->resource->standard_dial,
            'remote_dial'            => $this->resource->remote_dial,
            'temperature_range'      => $this->resource->temperature_range,
            'height'                 => $this->resource->height,
            'length'                 => $this->resource->length,
            'width'                  => $this->resource->width,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'type_of_gas'            => ['type' => ['string', 'null']],
                'stages'                 => ['type' => ['integer', 'null']],
                'capacity'               => ['type' => ['string', 'null']],
                'outlet_orientation'     => ['type' => ['string', 'null']],
                'reducer_bushing'        => ['type' => ['string', 'null']],
                'inlet_size'             => ['type' => ['string', 'null']],
                'outlet_size_type'       => ['type' => ['string', 'null']],
                'pilot_outlet_size'      => ['type' => ['string', 'null']],
                'factory_settings'       => ['type' => ['string', 'null']],
                'max_inlet_pressure'     => ['type' => ['string', 'null']],
                'min_adjustable_setting' => ['type' => ['string', 'null']],
                'max_adjustable_setting' => ['type' => ['string', 'null']],
                'terminal_type'          => ['type' => ['string', 'null']],
                'electrical_rating'      => ['type' => ['string', 'null']],
                'side_outlet_size_type'  => ['type' => ['string', 'null']],
                'gas_cock_dial_markings' => ['type' => ['string', 'null']],
                'ambient_temperature'    => ['type' => ['string', 'null']],
                'amp_rating'             => ['type' => ['string', 'null']],
                'capillary_length'       => ['type' => ['string', 'null']],
                'standard_dial'          => ['type' => ['string', 'null']],
                'remote_dial'            => ['type' => ['string', 'null']],
                'temperature_range'      => ['type' => ['string', 'null']],
                'height'                 => ['type' => ['string', 'null']],
                'length'                 => ['type' => ['string', 'null']],
                'width'                  => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'type_of_gas',
                'stages',
                'capacity',
                'outlet_orientation',
                'reducer_bushing',
                'inlet_size',
                'outlet_size_type',
                'pilot_outlet_size',
                'factory_settings',
                'max_inlet_pressure',
                'min_adjustable_setting',
                'max_adjustable_setting',
                'terminal_type',
                'electrical_rating',
                'side_outlet_size_type',
                'gas_cock_dial_markings',
                'ambient_temperature',
                'amp_rating',
                'capillary_length',
                'standard_dial',
                'remote_dial',
                'temperature_range',
                'height',
                'length',
                'width',
            ],
            'additionalProperties' => false,
        ];
    }
}
