<?php

namespace Database\Factories;

use App\Models\Flag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Str;

/**
 * @method Collection|Flag create($attributes = [], ?Model $parent = null)
 * @method Collection|Flag make($attributes = [], ?Model $parent = null)
 */
class FlagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'           => Str::slug($this->faker->sentence(3)),
            'flaggable_type' => Relation::getAliasByModel(User::class),
            'flaggable_id'   => User::factory(),
        ];
    }

    public function usingModel(Model $model): self
    {
        return $this->state(function() use ($model) {
            return [
                'flaggable_type' => Relation::getAliasByModel(get_class($model)),
                'flaggable_id'   => $model,
            ];
        });
    }
}
