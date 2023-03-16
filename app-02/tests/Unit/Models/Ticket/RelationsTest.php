<?php

namespace Tests\Unit\Models\Ticket;

use App\Models\Agent;
use App\Models\Communication;
use App\Models\Session;
use App\Models\Subject;
use App\Models\Ticket;
use App\Models\TicketReview;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Ticket $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Ticket::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_subject()
    {
        $related = $this->instance->subject()->first();

        $this->assertInstanceOf(Subject::class, $related);
    }

    /** @test */
    public function it_has_sessions()
    {
        Session::factory()->usingTicket($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->sessions()->get();

        $this->assertCorrectRelation($related, Session::class);
    }

    /** @test */
    public function it_has_communications()
    {
        $session = Session::factory()->usingTicket($this->instance)->create();
        Communication::factory()->usingSession($session)->count(10)->create();

        $related = $this->instance->communications()->get();

        $this->assertCorrectRelation($related, Communication::class);
    }

    /** @test */
    public function it_has_reviewer_agents()
    {
        TicketReview::factory()->usingTicket($this->instance)->count(10)->create();

        $related = $this->instance->reviewers()->get();

        $this->assertCorrectRelation($related, Agent::class);
    }

    /** @test */
    public function it_has_ticket_reviews()
    {
        TicketReview::factory()->usingTicket($this->instance)->count(10)->create();

        $related = $this->instance->ticketReviews()->get();

        $this->assertCorrectRelation($related, TicketReview::class);
    }
}
