<?php

namespace Database\Factories;

use App\Models\Compressor;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Compressor create($attributes = [], ?Model $parent = null)
 * @method Collection|Compressor make($attributes = [], ?Model $parent = null)
 */
class CompressorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->compressor(),
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
