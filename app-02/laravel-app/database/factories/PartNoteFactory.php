<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\PartNote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|PartNote create($attributes = [], ?Model $parent = null)
 * @method Collection|PartNote make($attributes = [], ?Model $parent = null)
 */
class PartNoteFactory extends Factory
{
    public function definition()
    {
        return [
            'part_id' => Part::factory(),
            'value'   => $this->faker->text(),
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
}
