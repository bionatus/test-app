<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Contactor;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Contactor $resource
 */
class ContactorResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Contactor $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'poles'                 => $this->resource->poles,
            'shunts'                => $this->resource->shunts,
            'coil_voltage'          => $this->resource->coil_voltage,
            'operating_voltage'     => $this->resource->operating_voltage,
            'ph'                    => $this->resource->ph,
            'hz'                    => $this->resource->hz,
            'fla'                   => $this->resource->fla,
            'lra'                   => $this->resource->lra,
            'connection_type'       => $this->resource->connection_type,
            'termination_type'      => $this->resource->termination_type,
            'resistive_amps'        => $this->resource->resistive_amps,
            'noninductive_amps'     => $this->resource->noninductive_amps,
            'auxialliary_contacts'  => $this->resource->auxialliary_contacts,
            'push_to_test_window'   => $this->resource->push_to_test_window,
            'contactor_type'        => $this->resource->contactor_type,
            'height'                => $this->resource->height,
            'width'                 => $this->resource->width,
            'length'                => $this->resource->length,
            'coil_type'             => $this->resource->coil_type,
            'max_hp'                => $this->resource->max_hp,
            'fuse_clip_size'        => $this->resource->fuse_clip_size,
            'enclosure_type'        => $this->resource->enclosure_type,
            'temperature_rating'    => $this->resource->temperature_rating,
            'current_setting_range' => $this->resource->current_setting_range,
            'reset_type'            => $this->resource->reset_type,
            'accessories'           => $this->resource->accessories,
            'overload_relays'       => $this->resource->overload_relays,
            'overload_time'         => $this->resource->overload_time,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'poles'                 => ['type' => ['string', 'null']],
                'shunts'                => ['type' => ['string', 'null']],
                'coil_voltage'          => ['type' => ['integer', 'null']],
                'operating_voltage'     => ['type' => ['string', 'null']],
                'ph'                    => ['type' => ['string', 'null']],
                'hz'                    => ['type' => ['string', 'null']],
                'fla'                   => ['type' => ['string', 'null']],
                'lra'                   => ['type' => ['string', 'null']],
                'connection_type'       => ['type' => ['string', 'null']],
                'termination_type'      => ['type' => ['string', 'null']],
                'resistive_amps'        => ['type' => ['string', 'null']],
                'noninductive_amps'     => ['type' => ['integer', 'null']],
                'auxialliary_contacts'  => ['type' => ['string', 'null']],
                'push_to_test_window'   => ['type' => ['string', 'null']],
                'contactor_type'        => ['type' => ['string', 'null']],
                'height'                => ['type' => ['string', 'null']],
                'width'                 => ['type' => ['string', 'null']],
                'length'                => ['type' => ['string', 'null']],
                'coil_type'             => ['type' => ['string', 'null']],
                'max_hp'                => ['type' => ['string', 'null']],
                'fuse_clip_size'        => ['type' => ['integer', 'null']],
                'enclosure_type'        => ['type' => ['string', 'null']],
                'temperature_rating'    => ['type' => ['string', 'null']],
                'current_setting_range' => ['type' => ['string', 'null']],
                'reset_type'            => ['type' => ['string', 'null']],
                'accessories'           => ['type' => ['string', 'null']],
                'overload_relays'       => ['type' => ['string', 'null']],
                'overload_time'         => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'poles',
                'shunts',
                'coil_voltage',
                'operating_voltage',
                'ph',
                'hz',
                'fla',
                'lra',
                'connection_type',
                'termination_type',
                'resistive_amps',
                'noninductive_amps',
                'auxialliary_contacts',
                'push_to_test_window',
                'contactor_type',
                'height',
                'width',
                'length',
                'coil_type',
                'max_hp',
                'fuse_clip_size',
                'enclosure_type',
                'temperature_rating',
                'current_setting_range',
                'reset_type',
                'accessories',
                'overload_relays',
                'overload_time',
            ],
            'additionalProperties' => false,
        ];
    }
}
