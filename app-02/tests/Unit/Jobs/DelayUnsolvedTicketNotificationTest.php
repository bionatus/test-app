<?php

namespace Tests\Unit\Jobs;

use App\Jobs\DelayUnsolvedTicketNotification;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\Tech\UnsolvedTicketNotification;
use Mockery;
use Notification;
use Tests\TestCase;

class DelayUnsolvedTicketNotificationTest extends TestCase
{
    /** @test */
    public function it_fails_if_delay_is_not_24_hours()
    {
        $job = new DelayUnsolvedTicketNotification(new AgentCall());

        $this->assertSame(24 * 60 * 60, $job->delay);
    }

    /** @test */
    public function it_fails_if_connection_is_not_database()
    {
        $job = new DelayUnsolvedTicketNotification(new AgentCall());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_does_not_fire_a_notification_when_there_is_no_ticket()
    {
        Notification::fake();
        $agentCall = new AgentCall();
        $agentCall->setRelation('call', $call = new Call());
        $call->setRelation('communication', $communication = new Communication());
        $communication->setRelation('session', new Session());

        $job = new DelayUnsolvedTicketNotification($agentCall);
        $job->handle();

        Notification::assertNothingSent();
    }

    /** @test */
    public function it_does_not_fire_a_notification_when_ticket_is_closed()
    {
        Notification::fake();

        $agentCall = new AgentCall();
        $agentCall->setRelation('call', $call = new Call());
        $call->setRelation('communication', $communication = new Communication());
        $communication->setRelation('session', $session = new Session());
        $session->setRelation('ticket', $ticket = Mockery::mock(Ticket::class));
        $ticket->shouldReceive('isOpen')->withNoArgs()->once()->andReturnFalse();

        $job = new DelayUnsolvedTicketNotification($agentCall);
        $job->handle();

        Notification::assertNothingSent();
    }

    /** @test
     * @throws \Exception
     */
    public function it_fires_an_unsolved_ticket_notification()
    {
        Notification::fake();

        $agentCall = new AgentCall();
        $agentCall->setRelation('call', $call = new Call());
        $call->setRelation('communication', $communication = new Communication());
        $communication->setRelation('session', $session = new Session());
        $session->setRelation('ticket', $ticket = Mockery::mock(Ticket::class));
        $session->setRelation('user', $user = new User());
        $ticket->shouldReceive('isOpen')->withNoArgs()->once()->andReturnTrue();

        $job = new DelayUnsolvedTicketNotification($agentCall);
        $job->handle();

        Notification::assertSentTo($user, UnsolvedTicketNotification::class);
    }
}
