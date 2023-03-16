<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\Wheel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Wheel create($attributes = [], ?Model $parent = null)
 * @method Collection|Wheel make($attributes = [], ?Model $parent = null)
 */
class WheelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->wheel(),
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
