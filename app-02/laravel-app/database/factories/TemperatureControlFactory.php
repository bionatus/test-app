<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\TemperatureControl;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|TemperatureControl create($attributes = [], ?Model $parent = null)
 * @method Collection|TemperatureControl make($attributes = [], ?Model $parent = null)
 */
class TemperatureControlFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->temperatureControl(),
        ];
    }

    public function usingPart(Part $part): self
    {
        return $this->state(function() use ($part) {
            return [
                'id' => $part,
            ];
        });
    }
}
