<?php

namespace Database\Factories;

use App\Models\SupplierListView;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SupplierListView create($attributes = [], ?Model $parent = null)
 * @method Collection|SupplierListView make($attributes = [], ?Model $parent = null)
 */
class SupplierListViewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
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
