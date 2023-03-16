<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\Replacement;
use App\Models\SingleReplacement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SingleReplacement create($attributes = [], ?Model $parent = null)
 * @method Collection|SingleReplacement make($attributes = [], ?Model $parent = null)
 */
class SingleReplacementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'                  => Replacement::factory()->single(),
            'replacement_part_id' => Part::factory(),
        ];
    }

    public function usingReplacement(Replacement $replacement): self
    {
        return $this->state(function() use ($replacement) {
            return [
                'id' => $replacement,
            ];
        });
    }

    public function usingPart(Part $part): self
    {
        return $this->state(function() use ($part) {
            return [
                'replacement_part_id' => $part,
            ];
        });
    }
}
