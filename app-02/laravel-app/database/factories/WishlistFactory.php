<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Wishlist create($attributes = [], ?Model $parent = null)
 * @method Collection|Wishlist createQuietly($attributes = [], ?Model $parent = null)
 * @method Collection|Wishlist make($attributes = [], ?Model $parent = null)
 */
class WishlistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'    => $this->faker->unique()->uuid,
            'user_id' => User::factory(),
            'name'    => $this->faker->name,
        ];
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }
}
