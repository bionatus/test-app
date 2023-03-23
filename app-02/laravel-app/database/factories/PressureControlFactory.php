<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PressureControl;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|PressureControl create($attributes = [], ?Model $parent = null)
 * @method Collection|PressureControl make($attributes = [], ?Model $parent = null)
 */
class PressureControlFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->pressureControl(),
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
