<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Subtopic;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Subtopic create($attributes = [], ?Model $parent = null)
 * @method Collection|Subtopic make($attributes = [], ?Model $parent = null)
 */
class SubtopicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'       => Subject::factory()->subtopic(),
            'topic_id' => Topic::factory(),
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

    public function usingTopic(Topic $topic): self
    {
        return $this->state(function() use ($topic) {
            return [
                'topic_id' => $topic,
            ];
        });
    }
}
