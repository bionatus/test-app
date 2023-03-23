<?php

namespace Database\Factories;

use App\Models\CrankcaseHeater;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|CrankcaseHeater create($attributes = [], ?Model $parent = null)
 * @method Collection|CrankcaseHeater make($attributes = [], ?Model $parent = null)
 */
class CrankcaseHeaterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->crankcaseHeater(),
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
