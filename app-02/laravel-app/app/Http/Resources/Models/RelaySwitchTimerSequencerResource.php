<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\RelaySwitchTimerSequencer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property RelaySwitchTimerSequencer $resource
 */
class RelaySwitchTimerSequencerResource extends JsonResource implements HasJsonSchema
{
    public function __construct(RelaySwitchTimerSequencer $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'poles'                 => $this->resource->poles,
            'action'                => $this->resource->action,
            'coil_voltage'          => $this->resource->coil_voltage,
            'ph'                    => $this->resource->ph,
            'hz'                    => $this->resource->hz,
            'fla'                   => $this->resource->fla,
            'operating_voltage'     => $this->resource->operating_voltage,
            'mounting_base'         => $this->resource->mounting_base,
            'terminal_type'         => $this->resource->terminal_type,
            'mounting_relay'        => $this->resource->mounting_relay,
            'delay_on_make'         => $this->resource->delay_on_make,
            'delay_on_break'        => $this->resource->delay_on_break,
            'adjustable'            => $this->resource->adjustable,
            'fused'                 => $this->resource->fused,
            'throw_type'            => $this->resource->throw_type,
            'mounting_type'         => $this->resource->mounting_type,
            'base_type'             => $this->resource->base_type,
            'status_indicator'      => $this->resource->status_indicator,
            'options'               => $this->resource->options,
            'ac_contact_rating'     => $this->resource->ac_contact_rating,
            'dc_contact_rating'     => $this->resource->dc_contact_rating,
            'socket_code'           => $this->resource->socket_code,
            'number_of_pins'        => $this->resource->number_of_pins,
            'max_switching_voltage' => $this->resource->max_switching_voltage,
            'min_switching_voltage' => $this->resource->min_switching_voltage,
            'service_life'          => $this->resource->service_life,
            'm1_m2_on_time'         => $this->resource->m1_m2_on_time,
            'm1_m2_off_time'        => $this->resource->m1_m2_off_time,
            'm3_m4_on_time'         => $this->resource->m3_m4_on_time,
            'm3_m4_off_time'        => $this->resource->m3_m4_off_time,
            'm5_m6_on_time'         => $this->resource->m5_m6_on_time,
            'm5_m6_off_time'        => $this->resource->m5_m6_off_time,
            'm7_m8_on_time'         => $this->resource->m7_m8_on_time,
            'm7_m8_off_time'        => $this->resource->m7_m8_off_time,
            'm9_m10_on_time'        => $this->resource->m9_m10_on_time,
            'm9_m10_off_time'       => $this->resource->m9_m10_off_time,
            'resistive_watts'       => $this->resource->resistive_watts,
            'lra'                   => $this->resource->lra,
            'pilot_duty'            => $this->resource->pilot_duty,
            'ambient_temperature'   => $this->resource->ambient_temperature,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'poles'                 => ['type' => ['string', 'null']],
                'action'                => ['type' => ['string', 'null']],
                'coil_voltage'          => ['type' => ['string', 'null']],
                'ph'                    => ['type' => ['integer', 'null']],
                'hz'                    => ['type' => ['string', 'null']],
                'fla'                   => ['type' => ['string', 'null']],
                'lra'                   => ['type' => ['string', 'null']],
                'operating_voltage'     => ['type' => ['integer', 'null']],
                'mounting_base'         => ['type' => ['string', 'null']],
                'terminal_type'         => ['type' => ['string', 'null']],
                'mounting_relay'        => ['type' => ['string', 'null']],
                'delay_on_make'         => ['type' => ['string', 'null']],
                'delay_on_break'        => ['type' => ['string', 'null']],
                'adjustable'            => ['type' => ['string', 'null']],
                'fused'                 => ['type' => ['boolean', 'null']],
                'throw_type'            => ['type' => ['string', 'null']],
                'mounting_type'         => ['type' => ['string', 'null']],
                'base_type'             => ['type' => ['string', 'null']],
                'status_indicator'      => ['type' => ['string', 'null']],
                'options'               => ['type' => ['string', 'null']],
                'ac_contact_rating'     => ['type' => ['string', 'null']],
                'dc_contact_rating'     => ['type' => ['string', 'null']],
                'socket_code'           => ['type' => ['string', 'null']],
                'number_of_pins'        => ['type' => ['integer', 'null']],
                'max_switching_voltage' => ['type' => ['string', 'null']],
                'min_switching_voltage' => ['type' => ['string', 'null']],
                'service_life'          => ['type' => ['string', 'null']],
                'm1_m2_on_time'         => ['type' => ['string', 'null']],
                'm1_m2_off_time'        => ['type' => ['string', 'null']],
                'm3_m4_on_time'         => ['type' => ['string', 'null']],
                'm3_m4_off_time'        => ['type' => ['string', 'null']],
                'm5_m6_on_time'         => ['type' => ['string', 'null']],
                'm5_m6_off_time'        => ['type' => ['string', 'null']],
                'm7_m8_on_time'         => ['type' => ['string', 'null']],
                'm7_m8_off_time'        => ['type' => ['string', 'null']],
                'm9_m10_on_time'        => ['type' => ['string', 'null']],
                'm9_m10_off_time'       => ['type' => ['string', 'null']],
                'resistive_watts'       => ['type' => ['string', 'null']],
                'pilot_duty'            => ['type' => ['string', 'null']],
                'ambient_temperature'   => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'poles',
                'action',
                'coil_voltage',
                'ph',
                'hz',
                'fla',
                'lra',
                'operating_voltage',
                'mounting_base',
                'terminal_type',
                'mounting_relay',
                'delay_on_make',
                'delay_on_break',
                'adjustable',
                'fused',
                'throw_type',
                'mounting_type',
                'base_type',
                'status_indicator',
                'options',
                'ac_contact_rating',
                'dc_contact_rating',
                'socket_code',
                'number_of_pins',
                'max_switching_voltage',
                'min_switching_voltage',
                'service_life',
                'm1_m2_on_time',
                'm1_m2_off_time',
                'm3_m4_on_time',
                'm3_m4_off_time',
                'm5_m6_on_time',
                'm5_m6_off_time',
                'm7_m8_on_time',
                'm7_m8_off_time',
                'm9_m10_on_time',
                'm9_m10_off_time',
                'resistive_watts',
                'pilot_duty',
                'ambient_temperature',
            ],
            'additionalProperties' => false,
        ];
    }
}
