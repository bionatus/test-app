<?php

namespace Database\Factories;

use App\Models\Layout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Layout create($attributes = [], ?Model $parent = null)
 * @method Collection|Layout make($attributes = [], ?Model $parent = null)
 */
class LayoutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'version'  => $this->faker->unique()->regexify('[0-9]{1}\.[0-9]{1,2}\.[0-9]{1,2}'),
            'products' => [],
        ];
    }
}
