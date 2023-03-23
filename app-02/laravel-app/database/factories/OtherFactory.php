<?php

namespace Database\Factories;

use App\Models\Other;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Other create($attributes = [], ?Model $parent = null)
 * @method Collection|Other make($attributes = [], ?Model $parent = null)
 */
class OtherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->other(),
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
