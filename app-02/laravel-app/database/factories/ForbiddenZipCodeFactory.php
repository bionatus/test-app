<?php

namespace Database\Factories;

use App\Models\ForbiddenZipCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ForbiddenZipCode create($attributes = [], ?Model $parent = null)
 * @method Collection|ForbiddenZipCode make($attributes = [], ?Model $parent = null)
 */

class ForbiddenZipCodeFactory extends Factory
{
    public function definition()
    {
        return [
            'zip_code' => $this->faker->regexify('[1-9][0-9]{4}'),
        ];
    }
}
