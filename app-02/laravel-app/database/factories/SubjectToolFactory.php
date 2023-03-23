<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\SubjectTool;
use App\Models\Tool;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|SubjectTool create($attributes = [], ?Model $parent = null)
 * @method Collection|SubjectTool make($attributes = [], ?Model $parent = null)
 */
class SubjectToolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'tool_id'    => Tool::factory(),
        ];
    }

    public function usingSubject(Subject $subject): self
    {
        return $this->state(function() use ($subject) {
            return [
                'subject_id' => $subject,
            ];
        });
    }

    public function usingTool(Tool $tool): self
    {
        return $this->state(function() use ($tool) {
            return [
                'tool_id' => $tool,
            ];
        });
    }
}
