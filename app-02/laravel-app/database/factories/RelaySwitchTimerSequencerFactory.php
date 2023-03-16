<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\RelaySwitchTimerSequencer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|RelaySwitchTimerSequencer create($attributes = [], ?Model $parent = null)
 * @method Collection|RelaySwitchTimerSequencer make($attributes = [], ?Model $parent = null)
 */
class RelaySwitchTimerSequencerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Part::factory()->relaySwitchTimerSequencer(),
        ];
    }

    public function usingPart(Part $part): self
    {
        return $this->state(function() use ($part) {
            return [
                'id' => $part,
            ];
        });
    }
}
