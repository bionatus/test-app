<?php

namespace Database\Factories;

use App\Models\SupplierCompany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SupplierCompany create($attributes = [], ?Model $parent = null)
 * @method Collection|SupplierCompany make($attributes = [], ?Model $parent = null)
 */
class SupplierCompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
        ];
    }

    public function withEmail(): self
    {
        return $this->state(function() {
            return [
                'email' => $this->faker->unique()->email,
            ];
        });
    }
}
