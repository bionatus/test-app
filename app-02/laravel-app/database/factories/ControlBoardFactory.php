<?php

namespace Database\Factories;

use App\Models\ControlBoard;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ControlBoard create($attributes = [], ?Model $parent = null)
 * @method Collection|ControlBoard make($attributes = [], ?Model $parent = null)
 */
class ControlBoardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->controlBoard(),
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
