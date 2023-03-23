<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\RecommendedReplacement;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|RecommendedReplacement create($attributes = [], ?Model $parent = null)
 * @method Collection|RecommendedReplacement make($attributes = [], ?Model $parent = null)
 * @method Collection|RecommendedReplacement createQuietly($attributes = [], ?Model $parent = null)
 */
class RecommendedReplacementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id'      => Supplier::factory(),
            'original_part_id' => Part::factory(),
            'brand'            => $this->faker->company,
            'part_number'      => $this->faker->ean13,
        ];
    }

    public function usingSupplier(Supplier $supplier): self
    {
        return $this->state(function() use ($supplier) {
            return [
                'supplier_id' => $supplier,
            ];
        });
    }

    public function usingPart(Part $part): self
    {
        return $this->state(function() use ($part) {
            return [
                'original_part_id' => $part,
            ];
        });
    }
}
