<?php

namespace Database\Factories;

use App\Models\GasValve;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|GasValve create($attributes = [], ?Model $parent = null)
 * @method Collection|GasValve make($attributes = [], ?Model $parent = null)
 */
class GasValveFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->gasValve(),
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
