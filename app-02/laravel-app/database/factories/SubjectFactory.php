<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|Subject create($attributes = [], ?Model $parent = null)
 * @method Collection|Subject make($attributes = [], ?Model $parent = null)
 */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fn(array $attributes) => Str::slug($attributes['name']),
            'name' => $this->faker->unique()->name,
            'type' => Subject::TYPE_TOPIC,
        ];
    }

    public function topic(): self
    {
        return $this->state(function() {
            return [
                'type' => Subject::TYPE_TOPIC,
            ];
        });
    }

    public function subtopic(): self
    {
        return $this->state(function() {
            return [
                'type' => Subject::TYPE_SUBTOPIC,
            ];
        });
    }
}
