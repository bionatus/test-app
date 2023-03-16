<?php

namespace Database\Factories;

use App\Models\Replacement;
use App\Models\ReplacementNote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ReplacementNote create($attributes = [], ?Model $parent = null)
 * @method Collection|ReplacementNote make($attributes = [], ?Model $parent = null)
 */
class ReplacementNoteFactory extends Factory
{
    public function definition()
    {
        return [
            'replacement_id' => Replacement::factory(),
            'value'          => $this->faker->text(),
        ];
    }

    public function usingReplacement(Replacement $replacement): self
    {
        return $this->state(function() use ($replacement) {
            return [
                'replacement_id' => $replacement,
            ];
        });
    }
}
