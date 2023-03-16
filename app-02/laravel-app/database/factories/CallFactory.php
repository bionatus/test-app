<?php

namespace Database\Factories;

use App\Models\Call;
use App\Models\Communication;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Call create($attributes = [], ?Model $parent = null)
 * @method Collection|Call make($attributes = [], ?Model $parent = null)
 */
class CallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'     => Communication::factory()->call(),
            'status' => Call::STATUS_IN_PROGRESS,
        ];
    }

    public function usingCommunication(Communication $communication): self
    {
        return $this->state(function() use ($communication) {
            return [
                'id' => $communication,
            ];
        });
    }

    public function invalid(): self
    {
        return $this->state(function() {
            return [
                'status' => Call::STATUS_INVALID,
            ];
        });
    }

    public function inProgress(): self
    {
        return $this->state(function() {
            return [
                'status' => Call::STATUS_IN_PROGRESS,
            ];
        });
    }

    public function completed(): self
    {
        return $this->state(function() {
            return [
                'status' => Call::STATUS_COMPLETED,
            ];
        });
    }
}
