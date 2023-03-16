<?php

namespace Database\Factories;

use App\Models\FanBlade;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|FanBlade create($attributes = [], ?Model $parent = null)
 * @method Collection|FanBlade make($attributes = [], ?Model $parent = null)
 */
class FanBladeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->fanBlade(),
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
