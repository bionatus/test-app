<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Part;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Part $resource
 */
class PartSpecificationResource extends JsonResource implements HasJsonSchema
{
    const TYPE_SPECIFICATIONS_MAP = [
        Part::TYPE_AIR_FILTER                   => AirFilterResource::class,
        Part::TYPE_BELT                         => BeltResource::class,
        Part::TYPE_CAPACITOR                    => CapacitorResource::class,
        Part::TYPE_COMPRESSOR                   => CompressorResource::class,
        Part::TYPE_CONTACTOR                    => ContactorResource::class,
        Part::TYPE_CONTROL_BOARD                => ControlBoardResource::class,
        Part::TYPE_CRANKCASE_HEATER             => CrankcaseHeaterResource::class,
        Part::TYPE_FAN_BLADE                    => FanBladeResource::class,
        Part::TYPE_FILTER_DRIER_AND_CORE        => FilterDrierAndCoreResource::class,
        Part::TYPE_GAS_VALVE                    => GasValveResource::class,
        Part::TYPE_HARD_START_KIT               => HardStartKitResource::class,
        Part::TYPE_IGNITER                      => IgniterResource::class,
        Part::TYPE_METERING_DEVICE              => MeteringDeviceResource::class,
        Part::TYPE_MOTOR                        => MotorResource::class,
        Part::TYPE_PRESSURE_CONTROL             => PressureControlResource::class,
        Part::TYPE_RELAY_SWITCH_TIMER_SEQUENCER => RelaySwitchTimerSequencerResource::class,
        Part::TYPE_SENSOR                       => SensorResource::class,
        Part::TYPE_SHEAVE_AND_PULLEY            => SheaveAndPulleyResource::class,
        Part::TYPE_TEMPERATURE_CONTROL          => TemperatureControlResource::class,
        Part::TYPE_WHEEL                        => WheelResource::class,
        Part::TYPE_OTHER                        => OtherResource::class,
    ];

    public function __construct(Part $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $resource = $this->detailResource($this->resource->detail);

        return $resource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return [
            'oneOf' => [
                AirFilterResource::jsonSchema(),
                BeltResource::jsonSchema(),
                CapacitorResource::jsonSchema(),
                CompressorResource::jsonSchema(),
                ContactorResource::jsonSchema(),
                ControlBoardResource::jsonSchema(),
                CrankcaseHeaterResource::jsonSchema(),
                FanBladeResource::jsonSchema(),
                FilterDrierAndCoreResource::jsonSchema(),
                GasValveResource::jsonSchema(),
                HardStartKitResource::jsonSchema(),
                IgniterResource::jsonSchema(),
                MeteringDeviceResource::jsonSchema(),
                MotorResource::jsonSchema(),
                OtherResource::jsonSchema(),
                PressureControlResource::jsonSchema(),
                RelaySwitchTimerSequencerResource::jsonSchema(),
                SensorResource::jsonSchema(),
                SheaveAndPulleyResource::jsonSchema(),
                TemperatureControlResource::jsonSchema(),
                WheelResource::jsonSchema(),
                [
                    'type' => ['array'],
                ],
            ],
        ];
    }

    private function detailResource(Model $partDetail): ?JsonResource
    {
        $type = $this->resource->type;

        if (!($detailClass = self::TYPE_SPECIFICATIONS_MAP[$type])) {
            return null;
        }

        return new $detailClass($partDetail);
    }
}
