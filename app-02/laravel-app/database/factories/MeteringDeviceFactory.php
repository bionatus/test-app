<?php

namespace Database\Factories;

use App\Models\MeteringDevice;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|MeteringDevice create($attributes = [], ?Model $parent = null)
 * @method Collection|MeteringDevice make($attributes = [], ?Model $parent = null)
 */
class MeteringDeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->meteringDevice(),
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
