<?php

namespace Database\Factories;

use App\Models\Contactor;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Contactor create($attributes = [], ?Model $parent = null)
 * @method Collection|Contactor make($attributes = [], ?Model $parent = null)
 */
class ContactorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->contactor(),
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
