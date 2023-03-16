<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Collection|Ticket create($attributes = [], ?Model $parent = null)
 * @method Collection|Ticket make($attributes = [], ?Model $parent = null)
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'subject_id' => Subject::factory(),
            'uuid'       => $this->faker->unique()->uuid,
            'topic'      => $this->faker->text($this->faker->numberBetween(10, 100)),
        ];
    }

    public function usingUser(User $user): self
    {
        return $this->state(function() use ($user) {
            return [
                'user_id' => $user,
            ];
        });
    }

    public function usingSubject(Subject $subject): self
    {
        return $this->state(function() use ($subject) {
            return [
                'subject_id' => $subject,
            ];
        });
    }

    public function open(): self
    {
        return $this->state(function() {
            return [
                'closed_at' => null,
            ];
        });
    }

    public function closed(): self
    {
        return $this->state(function() {
            return [
                'closed_at' => Carbon::now(),
            ];
        });
    }
}
