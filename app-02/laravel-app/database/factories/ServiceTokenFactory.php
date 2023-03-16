<?php

namespace Database\Factories;

use App\Models\ServiceToken;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ServiceToken create($attributes = [], ?Model $parent = null)
 * @method Collection|ServiceToken make($attributes = [], ?Model $parent = null)
 */
class ServiceTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::REFRESH_TOKEN,
            'value'        => $this->faker->sentence(),
        ];
    }
}
