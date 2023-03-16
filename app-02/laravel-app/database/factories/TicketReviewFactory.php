<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Ticket;
use App\Models\TicketReview;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Collection|TicketReview create($attributes = [], ?Model $parent = null)
 * @method Collection|TicketReview make($attributes = [], ?Model $parent = null)
 */
class TicketReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory()->closed(),
            'agent_id'  => Agent::factory(),
            'rating'    => rand(1, 5),
        ];
    }

    public function usingTicket(Ticket $ticket): self
    {
        return $this->state(function() use ($ticket) {
            return [
                'ticket_id' => $ticket,
            ];
        });
    }

    public function usingAgent(Agent $agent): self
    {
        return $this->state(function() use ($agent) {
            return [
                'agent_id' => $agent,
            ];
        });
    }
}
