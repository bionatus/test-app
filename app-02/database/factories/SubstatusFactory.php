<?php

namespace Database\Factories;

use App\Models\Status;
use App\Models\Substatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|Substatus create($attributes = [], ?Model $parent = null)
 * @method Collection|Substatus make($attributes = [], ?Model $parent = null)
 */
class SubstatusFactory extends Factory
{
    public function definition()
    {
        return [
            'status_id' => Status::factory(),
            'name'      => $this->faker->unique()->name,
            'slug'      => function(array $attributes) {
                return Str::slug($attributes['name']);
            },
        ];
    }

    public function usingStatus(Status $status)
    {
        return $this->state(function() use ($status) {
            return [
                'status_id' => $status,
            ];
        });
    }
}
