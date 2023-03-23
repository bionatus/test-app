<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\SheaveAndPulley;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SheaveAndPulley create($attributes = [], ?Model $parent = null)
 * @method Collection|SheaveAndPulley make($attributes = [], ?Model $parent = null)
 */
class SheaveAndPulleyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->sheaveAndPulley(),
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
