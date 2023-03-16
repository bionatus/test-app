<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Topic create($attributes = [], ?Model $parent = null)
 * @method Collection|Topic make($attributes = [], ?Model $parent = null)
 */
class TopicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'          => Subject::factory()->topic(),
            'description' => $this->faker->word,
        ];
    }

    public function usingSubject(Subject $subject): self
    {
        return $this->state(function() use ($subject) {
            return [
                'id' => $subject,
            ];
        });
    }
}
