<?php

namespace Database\Factories;

use App\Models\SupportCallCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|SupportCallCategory create($attributes = [], ?Model $parent = null)
 * @method Collection|SupportCallCategory make($attributes = [], ?Model $parent = null)
 */
class SupportCallCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug'  => fn(array $attributes) => Str::slug($attributes['name']),
            'name'  => $this->faker->sentence(3),
            'phone' => $this->faker->phoneNumber,
        ];
    }

    public function usingParent(SupportCallCategory $supportCallCategory): self
    {
        return $this->state(function() use ($supportCallCategory) {
            return [
                'parent_id' => $supportCallCategory,
            ];
        });
    }

    public function name(string $name): self
    {
        return $this->state(function() use ($name) {
            return [
                'slug' => Str::slug($name),
                'name' => $name,
            ];
        });
    }

    public function sort(int $sort = null): self
    {
        $sort = $sort ?: $this->faker->randomDigit;

        return $this->state(function() use ($sort) {
            return [
                'sort' => $sort,
            ];
        });
    }
}
