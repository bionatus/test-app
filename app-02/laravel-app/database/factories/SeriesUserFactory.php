<?php

namespace Database\Factories;

use App\Models\Series;
use App\Models\SeriesUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SeriesUser create($attributes = [], ?Model $parent = null)
 * @method Collection|SeriesUser make($attributes = [], ?Model $parent = null)
 */
class SeriesUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'series_id' => Series::factory(),
        ];
    }

    public function usingSeries(Series $series): self
    {
        return $this->state(function() use ($series) {
            return [
                'series_id' => $series,
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
}
