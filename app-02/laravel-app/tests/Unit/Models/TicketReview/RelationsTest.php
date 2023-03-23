<?php

namespace Tests\Unit\Models\TicketReview;

use App\Models\Agent;
use App\Models\Ticket;
use App\Models\TicketReview;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property TicketReview $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = TicketReview::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_agent()
    {
        $related = $this->instance->agent()->first();

        $this->assertInstanceOf(Agent::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_ticket()
    {
        $related = $this->instance->ticket()->first();

        $this->assertInstanceOf(Ticket::class, $related);
    }
}
