<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartTip;
use App\Models\Tip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|PartTip create($attributes = [], ?Model $parent = null)
 * @method Collection|PartTip make($attributes = [], ?Model $parent = null)
 */
class PartTipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'part_id' => Part::factory(),
            'tip_id'  => Tip::factory(),
        ];
    }

    public function usingPart(Part $part): self
    {
        return $this->state(function() use ($part) {
            return [
                'part_id' => $part,
            ];
        });
    }

    public function usingTip(Tip $tip): self
    {
        return $this->state(function() use ($tip) {
            return [
                'tip_id' => $tip,
            ];
        });
    }
}
