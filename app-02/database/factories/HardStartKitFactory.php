<?php

namespace Database\Factories;

use App\Models\HardStartKit;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|HardStartKit create($attributes = [], ?Model $parent = null)
 * @method Collection|HardStartKit make($attributes = [], ?Model $parent = null)
 */
class HardStartKitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->hardStartKit(),
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
