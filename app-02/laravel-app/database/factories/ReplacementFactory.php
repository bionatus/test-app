<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\Replacement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Replacement create($attributes = [], ?Model $parent = null)
 * @method Collection|Replacement make($attributes = [], ?Model $parent = null)
 */
class ReplacementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'original_part_id' => Part::factory(),
            'uuid'             => $this->faker->unique()->uuid,
            'type'             => Replacement::TYPE_SINGLE,
        ];
    }

    public function usingPart(Part $part): self
    {
        return $this->state(function() use ($part) {
            return [
                'original_part_id' => $part,
            ];
        });
    }

    public function single(): self
    {
        return $this->state(function() {
            return [
                'type' => Replacement::TYPE_SINGLE,
            ];
        });
    }

    public function grouped(): self
    {
        return $this->state(function() {
            return [
                'type' => Replacement::TYPE_GROUPED,
            ];
        });
    }
}
