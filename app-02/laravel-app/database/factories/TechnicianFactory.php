<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TechnicianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'                => $this->faker->unique()->name,
            'code'                => $this->faker->regexify('[1-9][0-9]{4}'),
            'phone'               => $this->faker->unique()->regexify('[1-9]{1}[0-9]{14}'),
            'image'               => $this->faker->imageUrl(),
            'years_of_experience' => $this->faker->numberBetween(0, 20),
            'show_in_app'         => $this->faker->boolean(),
        ];
    }
}
