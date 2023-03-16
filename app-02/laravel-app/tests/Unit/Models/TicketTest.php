<?php

namespace Tests\Unit\Models;

use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TicketTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Ticket::tableName(), [
            'id',
            'user_id',
            'subject_id',
            'uuid',
            'topic',
            'rating',
            'comment',
            'closed_at',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function its_morph_alias_is_ticket()
    {
        $this->assertSame('ticket', Ticket::MORPH_ALIAS);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $ticket = Ticket::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($ticket->uuid, $ticket->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $ticket = Ticket::factory()->make(['uuid' => null]);
        $ticket->save();

        $this->assertNotNull($ticket->uuid);
    }

    /** @test */
    public function it_knows_if_is_closed()
    {
        $open   = Ticket::factory()->open()->create();
        $closed = Ticket::factory()->closed()->create();

        $this->assertFalse($open->isClosed());
        $this->assertTrue($closed->isClosed());
    }

    /** @test */
    public function it_knows_if_is_open()
    {
        $open   = Ticket::factory()->open()->create();
        $closed = Ticket::factory()->closed()->create();

        $this->assertTrue($open->isOpen());
        $this->assertFalse($closed->isOpen());
    }

    /** @test */
    public function it_knows_if_a_user_is_its_owner()
    {
        $notOwner = User::factory()->create();

        $ticket = Ticket::factory()->create();

        $this->assertFalse($ticket->isOwner($notOwner));
        $this->assertTrue($ticket->isOwner($ticket->user));
    }

    /** @test */
    public function it_sets_itself_as_closed_if_open()
    {
        $ticket = Ticket::factory()->open()->create();
        $ticket->close();

        $this->assertTrue($ticket->isClosed());
    }

    /** @test */
    public function it_remains_closed_and_modifies_nothing_if_already_closed()
    {
        $ticket = Ticket::factory()->closed()->create(['closed_at' => $closedAt = Carbon::now()->subDay()]);
        $ticket->close();

        $this->assertEquals($closedAt->toIso8601String(), $ticket->refresh()->closed_at->toIso8601String());
    }

    /** @test */
    public function it_knows_if_an_agent_is_a_participant()
    {
        $ticket          = Ticket::factory()->create();
        $activeAgentCall = AgentCall::factory()->completed()->create();
        $activeSession   = $activeAgentCall->call->communication->session;
        $activeSession->ticket()->associate(Ticket::factory()->create());
        $activeSession->save();
        $inactiveAgentCall = AgentCall::factory()->ringing()->create();
        $inactiveSession   = $inactiveAgentCall->call->communication->session;
        $inactiveSession->ticket()->associate(Ticket::factory()->create());
        $inactiveSession->save();

        $this->assertTrue($ticket->isActiveParticipant($activeAgentCall->agent));
        $this->assertFalse($ticket->isActiveParticipant($inactiveAgentCall->agent));
        $this->assertFalse($ticket->isActiveParticipant(Agent::factory()->create()));
    }
}
