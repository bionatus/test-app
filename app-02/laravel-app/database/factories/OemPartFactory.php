<?php

namespace Database\Factories;

use App\Models\Oem;
use App\Models\OemPart;
use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|OemPart create($attributes = [], ?Model $parent = null)
 * @method Collection|OemPart make($attributes = [], ?Model $parent = null)
 */
class OemPartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'oem_id'  => Oem::factory(),
            'part_id' => Part::factory(),
        ];
    }

    public function usingOem(Oem $oem): self
    {
        return $this->state(function() use ($oem) {
            return [
                'oem_id' => $oem,
            ];
        });
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
