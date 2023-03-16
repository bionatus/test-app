<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\AirFilterResource;
use App\Http\Resources\Models\BeltResource;
use App\Http\Resources\Models\CapacitorResource;
use App\Http\Resources\Models\CompressorResource;
use App\Http\Resources\Models\ContactorResource;
use App\Http\Resources\Models\ControlBoardResource;
use App\Http\Resources\Models\CrankcaseHeaterResource;
use App\Http\Resources\Models\FanBladeResource;
use App\Http\Resources\Models\FilterDrierAndCoreResource;
use App\Http\Resources\Models\GasValveResource;
use App\Http\Resources\Models\HardStartKitResource;
use App\Http\Resources\Models\IgniterResource;
use App\Http\Resources\Models\MeteringDeviceResource;
use App\Http\Resources\Models\MotorResource;
use App\Http\Resources\Models\OtherResource;
use App\Http\Resources\Models\PartSpecificationResource;
use App\Http\Resources\Models\PressureControlResource;
use App\Http\Resources\Models\RelaySwitchTimerSequencerResource;
use App\Http\Resources\Models\SensorResource;
use App\Http\Resources\Models\SheaveAndPulleyResource;
use App\Http\Resources\Models\TemperatureControlResource;
use App\Http\Resources\Models\WheelResource;
use App\Models\AirFilter;
use App\Models\Belt;
use App\Models\Capacitor;
use App\Models\Compressor;
use App\Models\Contactor;
use App\Models\ControlBoard;
use App\Models\CrankcaseHeater;
use App\Models\FanBlade;
use App\Models\FilterDrierAndCore;
use App\Models\GasValve;
use App\Models\HardStartKit;
use App\Models\Igniter;
use App\Models\MeteringDevice;
use App\Models\Motor;
use App\Models\Other;
use App\Models\Part;
use App\Models\PressureControl;
use App\Models\RelaySwitchTimerSequencer;
use App\Models\Sensor;
use App\Models\SheaveAndPulley;
use App\Models\TemperatureControl;
use App\Models\Wheel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PartSpecificationResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_has_correct_fields_all_resources($type, $resourceClass, $typePartClass)
    {
        $part = Part::factory()->create(['type' => $type]);
        ($typePartClass)::factory()->usingPart($part)->create();

        $resource = new PartSpecificationResource($part->fresh());
        $response = $resource->resolve();
        
        $data = (new $resourceClass($part->detail))->resolve();

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(PartSpecificationResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    public function dataProvider()
    {
        return [
            [Part::TYPE_AIR_FILTER, AirFilterResource::class, AirFilter::class],
            [Part::TYPE_BELT, BeltResource::class, Belt::class],
            [Part::TYPE_CAPACITOR, CapacitorResource::class, Capacitor::class],
            [Part::TYPE_COMPRESSOR, CompressorResource::class, Compressor::class],
            [Part::TYPE_CONTACTOR, ContactorResource::class, Contactor::class],
            [Part::TYPE_CONTROL_BOARD, ControlBoardResource::class, ControlBoard::class],
            [Part::TYPE_CRANKCASE_HEATER, CrankcaseHeaterResource::class, CrankcaseHeater::class],
            [Part::TYPE_FAN_BLADE, FanBladeResource::class, FanBlade::class],
            [Part::TYPE_FILTER_DRIER_AND_CORE, FilterDrierAndCoreResource::class, FilterDrierAndCore::class],
            [Part::TYPE_GAS_VALVE, GasValveResource::class, GasValve::class],
            [Part::TYPE_HARD_START_KIT, HardStartKitResource::class, HardStartKit::class],
            [Part::TYPE_IGNITER, IgniterResource::class, Igniter::class],
            [Part::TYPE_METERING_DEVICE, MeteringDeviceResource::class, MeteringDevice::class],
            [Part::TYPE_MOTOR, MotorResource::class, Motor::class],
            [Part::TYPE_PRESSURE_CONTROL, PressureControlResource::class, PressureControl::class],
            [
                Part::TYPE_RELAY_SWITCH_TIMER_SEQUENCER,
                RelaySwitchTimerSequencerResource::class,
                RelaySwitchTimerSequencer::class,
            ],
            [Part::TYPE_SENSOR, SensorResource::class, Sensor::class],
            [Part::TYPE_SHEAVE_AND_PULLEY, SheaveAndPulleyResource::class, SheaveAndPulley::class],
            [Part::TYPE_TEMPERATURE_CONTROL, TemperatureControlResource::class, TemperatureControl::class],
            [Part::TYPE_WHEEL, WheelResource::class, Wheel::class],
            [Part::TYPE_OTHER, OtherResource::class, Other::class],
        ];
    }
}
