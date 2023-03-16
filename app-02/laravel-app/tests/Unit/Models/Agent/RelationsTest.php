<?php

namespace Tests\Unit\Models\Agent;

use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Ticket;
use App\Models\TicketReview;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Agent $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Agent::factory()->create();
    }

    /** @test */
    public function it_is_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_has_calls()
    {
        AgentCall::factory()->usingAgent($this->instance)->count(10)->create();

        $related = $this->instance->calls()->get();

        $this->assertCorrectRelation($related, Call::class);
    }

    /** @test */
    public function it_has_agent_calls()
    {
        AgentCall::factory()->usingAgent($this->instance)->count(10)->create();

        $related = $this->instance->agentCalls()->get();

        $this->assertCorrectRelation($related, AgentCall::class);
    }

    /** @test */
    public function it_has_reviewed_tickets()
    {
        TicketReview::factory()->usingAgent($this->instance)->count(10)->create();

        $related = $this->instance->reviewedTickets()->get();

        $this->assertCorrectRelation($related, Ticket::class);
    }

    /** @test */
    public function it_has_ticket_reviews()
    {
        TicketReview::factory()->usingAgent($this->instance)->count(10)->create();

        $related = $this->instance->ticketReviews()->get();

        $this->assertCorrectRelation($related, TicketReview::class);
    }
}
