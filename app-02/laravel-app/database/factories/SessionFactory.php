<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\Subject;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|Session create($attributes = [], ?Model $parent = null)
 * @method Collection|Session make($attributes = [], ?Model $parent = null)
 */
class SessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'subject_id' => Subject::factory(),
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

    public function usingTicket(Ticket $ticket): self
    {
        return $this->state(function() use ($ticket) {
            return [
                'ticket_id' => $ticket,
            ];
        });
    }

    public function withTicket(): self
    {
        return $this->state(function() {
            return [
                'ticket_id' => Ticket::factory(),
            ];
        });
    }
}
