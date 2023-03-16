<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Sensor;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Sensor $resource
 */
class SensorResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Sensor $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'application'           => $this->resource->application,
            'signal_type'           => $this->resource->signal_type,
            'measurement_range'     => $this->resource->measurement_range,
            'connection_type'       => $this->resource->connection_type,
            'configuration'         => $this->resource->configuration,
            'number_of_wires'       => $this->resource->number_of_wires,
            'accuracy'              => $this->resource->accuracy,
            'enclosure_rating'      => $this->resource->enclosure_rating,
            'lead_length'           => $this->resource->lead_length,
            'operating_temperature' => $this->resource->operating_temperature,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'application'           => ['type' => ['string', 'null']],
                'signal_type'           => ['type' => ['string', 'null']],
                'measurement_range'     => ['type' => ['string', 'null']],
                'connection_type'       => ['type' => ['string', 'null']],
                'configuration'         => ['type' => ['string', 'null']],
                'number_of_wires'       => ['type' => ['integer', 'null']],
                'accuracy'              => ['type' => ['string', 'null']],
                'enclosure_rating'      => ['type' => ['string', 'null']],
                'lead_length'           => ['type' => ['string', 'null']],
                'operating_temperature' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'application',
                'signal_type',
                'measurement_range',
                'connection_type',
                'configuration',
                'number_of_wires',
                'accuracy',
                'enclosure_rating',
                'lead_length',
                'operating_temperature',
            ],
            'additionalProperties' => false,
        ];
    }
}
