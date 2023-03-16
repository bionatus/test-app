<?php

namespace Database\Factories;

use App\Models\AuthenticationCode;
use App\Models\Model;
use App\Models\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|AuthenticationCode create($attributes = [], ?Model $parent = null)
 * @method Collection|AuthenticationCode make($attributes = [], ?Model $parent = null)
 */
class AuthenticationCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'phone_id' => Phone::factory(),
            'type'     => AuthenticationCode::TYPE_LOGIN,
            'code'     => $this->faker->regexify('[1-9]{1}[0-9]{5}'),
        ];
    }

    public function usingPhone(Phone $phone): self
    {
        return $this->state(function() use ($phone) {
            return [
                'phone_id' => $phone,
            ];
        });
    }

    public function login(): self
    {
        return $this->state(function() {
            return [
                'type' => AuthenticationCode::TYPE_LOGIN,
            ];
        });
    }

    public function verification(): self
    {
        return $this->state(function() {
            return [
                'type' => AuthenticationCode::TYPE_VERIFICATION,
            ];
        });
    }
}
