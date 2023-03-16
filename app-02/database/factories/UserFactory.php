<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|User create($attributes = [], ?Model $parent = null)
 * @method Collection|User make($attributes = [], ?Model $parent = null)
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email'                 => $this->faker->unique()->email,
            'password'              => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'first_name'            => $this->faker->firstName,
            'last_name'             => $this->faker->lastName,
            'public_name'           => $this->faker->unique()->userName,
            'name'                  => fn(array $attributes
            ) => "{$attributes['first_name']} {$attributes['last_name']}",
            'manual_download_count' => 0,
        ];
    }

    public function photo(): self
    {
        return $this->state(function() {
            return [
                'photo' => rand(1000, 99999) . '.jpg',
            ];
        });
    }

    public function moderator(): self
    {
        return $this->state(function() {
            return [
                'email' => 'acurry@bionatusllc.com',
            ];
        });
    }

    public function verified(?CarbonInterface $value): self
    {
        return $this->state(function() use ($value) {
            return [
                'verified_at' => $value,
            ];
        });
    }

    public function disabled(?CarbonInterface $value): self
    {
        return $this->state(function() use ($value) {
            return [
                'disabled_at' => $value,
            ];
        });
    }

    public function accredited(?bool $value = true): self
    {
        return $this->state(function() use ($value) {
            return [
                'accreditated' => $value,
            ];
        });
    }

    public function registered(?bool $value = true): self
    {
        return $this->state(function() use ($value) {
            return [
                'registration_completed' => $value,
            ];
        });
    }

    public function full(): self
    {
        $now = Carbon::now();

        return $this->verified($now)->accredited()->registered()->state(function() use ($now) {
            return [
                'email_verified_at' => $now,
            ];
        });
    }
}
