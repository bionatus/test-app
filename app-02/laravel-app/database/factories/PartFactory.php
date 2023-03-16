<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Part;
use App\Models\Tip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Part create($attributes = [], ?Model $parent = null)
 * @method Collection|Part make($attributes = [], ?Model $parent = null)
 */
class PartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'     => Item::factory()->part(),
            'number' => $this->faker->regexify('[a-zA-Z0-9]{15}'),
            'brand'  => $this->faker->word,
            'type'   => Part::TYPE_AIR_FILTER,
        ];
    }

    public function functional(): self
    {
        return $this->state(function() {
            return [
                'type' => $this->faker->randomElement(Part::FUNCTIONAL_TYPES),
            ];
        });
    }

    public function belt(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_BELT,
            ];
        });
    }

    public function capacitor(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_CAPACITOR,
            ];
        });
    }

    public function compressor(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_COMPRESSOR,
            ];
        });
    }

    public function contactor(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_CONTACTOR,
            ];
        });
    }

    public function controlBoard(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_CONTROL_BOARD,
            ];
        });
    }

    public function crankcaseHeater(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_CRANKCASE_HEATER,
            ];
        });
    }

    public function fanBlade(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_FAN_BLADE,
            ];
        });
    }

    public function filterDrierAndCore(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_FILTER_DRIER_AND_CORE,
            ];
        });
    }

    public function gasValve(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_GAS_VALVE,
            ];
        });
    }

    public function hardStartKit(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_HARD_START_KIT,
            ];
        });
    }

    public function igniter(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_IGNITER,
            ];
        });
    }

    public function meteringDevice(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_METERING_DEVICE,
            ];
        });
    }

    public function motor(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_MOTOR,
            ];
        });
    }

    public function pressureControl(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_PRESSURE_CONTROL,
            ];
        });
    }

    public function relaySwitchTimerSequencer(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_RELAY_SWITCH_TIMER_SEQUENCER,
            ];
        });
    }

    public function sensor(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_SENSOR,
            ];
        });
    }

    public function sheaveAndPulley(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_SHEAVE_AND_PULLEY,
            ];
        });
    }

    public function temperatureControl(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_TEMPERATURE_CONTROL,
            ];
        });
    }

    public function wheel(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_WHEEL,
            ];
        });
    }

    public function other(): self
    {
        return $this->state(function() {
            return [
                'type' => Part::TYPE_OTHER,
            ];
        });
    }

    public function number(string $number): self
    {
        return $this->state(function() use ($number) {
            return [
                'number' => $number,
            ];
        });
    }

    public function usingTip(Tip $tip): self
    {
        return $this->state(function() use ($tip) {
            return [
                'tip_id' => $tip,
            ];
        });
    }
}
