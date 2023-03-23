<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Compressor;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Compressor $resource
 */
class CompressorResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Compressor $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'rated_refrigerant'      => $this->resource->rated_refrigerant,
            'oil_type'               => $this->resource->oil_type,
            'nominal_capacity_tons'  => $this->resource->nominal_capacity_tons,
            'nominal_capacity_btuh'  => $this->resource->nominal_capacity_btuh,
            'voltage'                => $this->resource->voltage,
            'ph'                     => $this->resource->ph,
            'hz'                     => $this->resource->hz,
            'run_capacitor'          => $this->resource->run_capacitor,
            'start_capacitor'        => $this->resource->start_capacitor,
            'connection_type'        => $this->resource->connection_type,
            'suction_inlet_diameter' => $this->resource->suction_inlet_diameter,
            'discharge_diameter'     => $this->resource->discharge_diameter,
            'number_of_cylinders'    => $this->resource->number_of_cylinders,
            'number_of_unloaders'    => $this->resource->number_of_unloaders,
            'crankcase_heater'       => $this->resource->crankcase_heater,
            'protection'             => $this->resource->protection,
            'speed'                  => $this->resource->speed,
            'eer'                    => $this->resource->eer,
            'displacement'           => $this->resource->displacement,
            'nominal_hp'             => $this->resource->nominal_hp,
            'nominal_power_watts'    => $this->resource->nominal_power_watts,
            'fla'                    => $this->resource->fla,
            'lra'                    => $this->resource->lra,
            'rpm'                    => $this->resource->rpm,
            'compressor_length'      => $this->resource->compressor_length,
            'compressor_width'       => $this->resource->compressor_width,
            'compressor_height'      => $this->resource->compressor_height,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'rated_refrigerant'      => ['type' => ['string', 'null']],
                'oil_type'               => ['type' => ['string', 'null']],
                'nominal_capacity_tons'  => ['type' => ['string', 'null']],
                'nominal_capacity_btuh'  => ['type' => ['string', 'null']],
                'voltage'                => ['type' => ['string', 'null']],
                'ph'                     => ['type' => ['string', 'null']],
                'hz'                     => ['type' => ['string', 'null']],
                'run_capacitor'          => ['type' => ['string', 'null']],
                'start_capacitor'        => ['type' => ['string', 'null']],
                'connection_type'        => ['type' => ['string', 'null']],
                'suction_inlet_diameter' => ['type' => ['string', 'null']],
                'discharge_diameter'     => ['type' => ['string', 'null']],
                'number_of_cylinders'    => ['type' => ['integer', 'null']],
                'number_of_unloaders'    => ['type' => ['integer', 'null']],
                'crankcase_heater'       => ['type' => ['boolean', 'null']],
                'protection'             => ['type' => ['string', 'null']],
                'speed'                  => ['type' => ['string', 'null']],
                'eer'                    => ['type' => ['number', 'null']],
                'displacement'           => ['type' => ['string', 'null']],
                'nominal_hp'             => ['type' => ['string', 'null']],
                'nominal_power_watts'    => ['type' => ['string', 'null']],
                'fla'                    => ['type' => ['string', 'null']],
                'lra'                    => ['type' => ['string', 'null']],
                'rpm'                    => ['type' => ['string', 'null']],
                'compressor_length'      => ['type' => ['string', 'null']],
                'compressor_width'       => ['type' => ['string', 'null']],
                'compressor_height'      => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'rated_refrigerant',
                'oil_type',
                'nominal_capacity_tons',
                'nominal_capacity_btuh',
                'voltage',
                'ph',
                'hz',
                'run_capacitor',
                'start_capacitor',
                'connection_type',
                'suction_inlet_diameter',
                'discharge_diameter',
                'number_of_cylinders',
                'number_of_unloaders',
                'crankcase_heater',
                'protection',
                'speed',
                'eer',
                'displacement',
                'nominal_hp',
                'nominal_power_watts',
                'fla',
                'lra',
                'rpm',
                'compressor_length',
                'compressor_width',
                'compressor_height',
            ],
            'additionalProperties' => false,
        ];
    }
}
