<?php

namespace Database\Factories;

use App\Models\NoteCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|NoteCategory create($attributes = [], ?Model $parent = null)
 * @method Collection|NoteCategory make($attributes = [], ?Model $parent = null)
 */
class NoteCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'slug' => function(array $attributes) {
                return Str::slug($attributes['name']);
            },
        ];
    }

    public function featured(): self
    {
        return $this->state(function() {
            return [
                'slug' => NoteCategory::SLUG_FEATURED,
            ];
        });
    }
}
