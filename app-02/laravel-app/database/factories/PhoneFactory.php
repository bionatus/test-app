<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\Phone;
use App\Models\User;
use App\Types\CountryDataType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Phone create($attributes = [], ?Model $parent = null)
 * @method Collection|Phone make($attributes = [], ?Model $parent = null)
 */
class PhoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'country_code' => CountryDataType::getPhoneCodes()->first(),
            'number'       => $this->faker->unique()->regexify('[1-9]{1}[0-9]{14}'),
        ];
    }

    public function unverified(): self
    {
        return $this->state(function() {
            return [
                'verified_at' => null,
            ];
        });
    }

    public function verified(): self
    {
        return $this->state(function() {
            return [
                'verified_at' => $this->faker->dateTime,
            ];
        });
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }

    public function withUser(): self
    {
        return $this->state(function() {
            return [
                'user_id' => User::factory(),
            ];
        });
    }
}
