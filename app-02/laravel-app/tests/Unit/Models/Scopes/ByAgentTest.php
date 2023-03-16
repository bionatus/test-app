<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Agent;
use App\Models\Scopes\ByAgent;
use App\Models\TicketReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByAgentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_agent_on_ticket_review_model()
    {
        $agent = Agent::factory()->create();
        TicketReview::factory()->usingAgent($agent)->count(2)->create();
        TicketReview::factory()->count(3)->create();

        $ticketReviews = TicketReview::scoped(new ByAgent($agent))->get();

        $this->assertCount(2, $ticketReviews);
    }
}
