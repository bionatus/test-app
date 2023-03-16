<?php

namespace Database\Factories;

use App\Models\Replacement;
use App\Models\ReplacementSource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|ReplacementSource create($attributes = [], ?Model $parent = null)
 * @method Collection|ReplacementSource make($attributes = [], ?Model $parent = null)
 */
class ReplacementSourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Replacement::factory()->single(),
        ];
    }

    public function usingReplacement(Replacement $replacement): self
    {
        return $this->state(function() use ($replacement) {
            return [
                'id' => $replacement,
            ];
        });
    }
}
