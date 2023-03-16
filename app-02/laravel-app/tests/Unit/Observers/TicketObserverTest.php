<?php

namespace Tests\Unit\Observers;

use App\Models\Ticket;
use App\Observers\TicketObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $ticket = Ticket::factory()->make(['uuid' => null]);

        $observer = new TicketObserver();

        $observer->creating($ticket);

        $this->assertNotNull($ticket->uuid);
    }
}
