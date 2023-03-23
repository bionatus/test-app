<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Igniter;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Igniter $resource
 */
class IgniterResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Igniter $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'application'                  => $this->resource->application,
            'gas_type'                     => $this->resource->gas_type,
            'voltage'                      => $this->resource->voltage,
            'terminal_type'                => $this->resource->terminal_type,
            'mounting'                     => $this->resource->mounting,
            'tip_style'                    => $this->resource->tip_style,
            'ceramic_block'                => $this->resource->ceramic_block,
            'pilot_btu'                    => $this->resource->pilot_btu,
            'orifice_diameter'             => $this->resource->orifice_diameter,
            'pilot_tube_length'            => $this->resource->pilot_tube_length,
            'lead_length'                  => $this->resource->lead_length,
            'sensor_type'                  => $this->resource->sensor_type,
            'steady_current'               => $this->resource->steady_current,
            'temp_rating'                  => $this->resource->temp_rating,
            'time_to_temp'                 => $this->resource->time_to_temp,
            'amperage'                     => $this->resource->amperage,
            'cold_resistance'              => $this->resource->cold_resistance,
            'max_current'                  => $this->resource->max_current,
            'compression_fitting_diameter' => $this->resource->compression_fitting_diameter,
            'probe_length'                 => $this->resource->probe_length,
            'rod_angle'                    => $this->resource->rod_angle,
            'length'                       => $this->resource->length,
            'height'                       => $this->resource->height,
            'width'                        => $this->resource->width,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'application'                  => ['type' => ['string', 'null']],
                'gas_type'                     => ['type' => ['string', 'null']],
                'voltage'                      => ['type' => ['string', 'null']],
                'terminal_type'                => ['type' => ['string', 'null']],
                'mounting'                     => ['type' => ['string', 'null']],
                'tip_style'                    => ['type' => ['string', 'null']],
                'ceramic_block'                => ['type' => ['string', 'null']],
                'pilot_btu'                    => ['type' => ['string', 'null']],
                'orifice_diameter'             => ['type' => ['string', 'null']],
                'pilot_tube_length'            => ['type' => ['string', 'null']],
                'lead_length'                  => ['type' => ['string', 'null']],
                'sensor_type'                  => ['type' => ['string', 'null']],
                'steady_current'               => ['type' => ['string', 'null']],
                'temp_rating'                  => ['type' => ['string', 'null']],
                'time_to_temp'                 => ['type' => ['string', 'null']],
                'amperage'                     => ['type' => ['string', 'null']],
                'cold_resistance'              => ['type' => ['string', 'null']],
                'max_current'                  => ['type' => ['string', 'null']],
                'compression_fitting_diameter' => ['type' => ['string', 'null']],
                'probe_length'                 => ['type' => ['string', 'null']],
                'rod_angle'                    => ['type' => ['string', 'null']],
                'length'                       => ['type' => ['string', 'null']],
                'height'                       => ['type' => ['string', 'null']],
                'width'                        => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'application',
                'gas_type',
                'voltage',
                'terminal_type',
                'mounting',
                'tip_style',
                'ceramic_block',
                'pilot_btu',
                'orifice_diameter',
                'pilot_tube_length',
                'lead_length',
                'sensor_type',
                'steady_current',
                'temp_rating',
                'time_to_temp',
                'amperage',
                'cold_resistance',
                'max_current',
                'compression_fitting_diameter',
                'probe_length',
                'rod_angle',
                'length',
                'height',
                'width',
            ],
            'additionalProperties' => false,
        ];
    }
}
