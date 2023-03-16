<?php

namespace Tests\Unit\Listeners\AgentCall;

use App;
use App\Events\AgentCall\Answered;
use App\Listeners\AgentCall\SendTechEngagedNotification;
use App\Models\Agent;
use App\Models\AgentCall;
use App\Notifications\Agent\TechEngagedNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class SendTechEngagedNotificationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendTechEngagedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_notification_to_an_agent()
    {
        Notification::fake();

        $mock      = Mockery::mock(Answered::class);
        $agentCall = new AgentCall();
        $agentCall->setRelation('agent', $agent = new Agent());

        $mock->shouldReceive('agentCall')->withNoArgs()->once()->andReturn($agentCall);

        $listener = App::make(SendTechEngagedNotification::class);
        $listener->handle($mock);

        Notification::assertSentTo($agent, TechEngagedNotification::class,
            function(TechEngagedNotification $notification) use ($agentCall) {
                $reflection = new ReflectionClass($notification);
                $property   = $reflection->getProperty('agentCall');
                $property->setAccessible(true);

                return $agentCall === $property->getValue($notification);
            });
    }
}
