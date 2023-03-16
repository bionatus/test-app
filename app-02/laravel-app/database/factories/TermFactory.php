<?php

namespace Database\Factories;

use App\Models\Term;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Term create($attributes = [], ?Model $parent = null)
 * @method Collection|Term make($attributes = [], ?Model $parent = null)
 */
class TermFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'       => $this->faker->text(26),
            'body'        => $this->faker->text(190),
            'link'        => $this->faker->url,
            'required_at' => $this->faker->date(),
        ];
    }
}
