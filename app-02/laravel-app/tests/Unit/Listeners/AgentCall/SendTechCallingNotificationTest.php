<?php

namespace Tests\Unit\Listeners\AgentCall;

use App;
use App\Events\AgentCall\Ringing;
use App\Listeners\AgentCall\SendTechCallingNotification;
use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Notifications\Agent\TechCallingNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class SendTechCallingNotificationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendTechCallingNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_notification_to_an_agent()
    {
        Notification::fake();

        $mock      = Mockery::mock(Ringing::class);
        $agentCall = new AgentCall();
        $agentCall->setRelation('agent', $agent = new Agent());
        $agentCall->setRelation('call', $call = new Call());
        $call->setRelation('communication', $communication = new Communication());
        $communication->setRelation('session', $session = new Session());

        $mock->shouldReceive('agentCall')->withNoArgs()->once()->andReturn($agentCall);

        $listener = App::make(SendTechCallingNotification::class);
        $listener->handle($mock);

        Notification::assertSentTo($agent, TechCallingNotification::class,
            function(TechCallingNotification $notification) use ($session) {
                $reflection = new ReflectionClass($notification);
                $property   = $reflection->getProperty('session');
                $property->setAccessible(true);

                return $session === $property->getValue($notification);
            });
    }
}
