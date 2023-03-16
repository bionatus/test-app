<?php

namespace Database\Factories;

use App\Models\Belt;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Belt create($attributes = [], ?Model $parent = null)
 * @method Collection|Belt make($attributes = [], ?Model $parent = null)
 */
class BeltFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->belt(),
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
