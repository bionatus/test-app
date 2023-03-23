<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\NoteCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * @method Collection|Note create($attributes = [], ?Model $parent = null)
 * @method Collection|Note make($attributes = [], ?Model $parent = null)
 */
class NoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug'             => fn(array $attributes) => Str::slug($attributes['title']),
            'title'            => $this->faker->text(26),
            'body'             => $this->faker->text(90),
            'note_category_id' => NoteCategory::factory(),
        ];
    }

    public function usingNoteCategory(NoteCategory $noteCategory): self
    {
        return $this->state(function() use ($noteCategory) {
            return [
                'note_category_id' => $noteCategory,
            ];
        });
    }
}
