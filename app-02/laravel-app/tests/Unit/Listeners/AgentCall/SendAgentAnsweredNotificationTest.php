<?php

namespace Tests\Unit\Listeners\AgentCall;

use App;
use App\Events\AgentCall\Answered;
use App\Listeners\AgentCall\SendAgentAnsweredNotification;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Models\User;
use App\Notifications\Tech\AgentAnsweredNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class SendAgentAnsweredNotificationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendAgentAnsweredNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_notification_to_a_technician()
    {
        Notification::fake();

        $mock      = Mockery::mock(Answered::class);
        $agentCall = new AgentCall();
        $agentCall->setRelation('call', $call = new Call());
        $call->setRelation('communication', $communication = new Communication());
        $communication->setRelation('session', $session = new Session());
        $session->setRelation('user', $user = new User());

        $mock->shouldReceive('agentCall')->withNoArgs()->once()->andReturn($agentCall);

        $listener = App::make(SendAgentAnsweredNotification::class);
        $listener->handle($mock);
        Notification::assertSentTo($user, AgentAnsweredNotification::class);
    }
}
