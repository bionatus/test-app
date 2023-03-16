<?php

namespace Database\Factories;

use App\Models\Igniter;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Igniter create($attributes = [], ?Model $parent = null)
 * @method Collection|Igniter make($attributes = [], ?Model $parent = null)
 */
class IgniterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->igniter(),
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
