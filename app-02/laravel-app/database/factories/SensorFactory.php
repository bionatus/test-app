<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Sensor create($attributes = [], ?Model $parent = null)
 * @method Collection|Sensor make($attributes = [], ?Model $parent = null)
 */
class SensorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->sensor(),
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
