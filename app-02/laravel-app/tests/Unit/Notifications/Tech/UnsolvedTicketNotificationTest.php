<?php

namespace Tests\Unit\Notifications\Tech;

use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\Tech\UnsolvedTicketNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use NotificationChannels\Fcm\FcmMessage;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class UnsolvedTicketNotificationTest extends TestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(UnsolvedTicketNotification::class, SendsPushNotification::class);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UnsolvedTicketNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        $firstName = 'John';

        $agentCall = new AgentCall();
        $agentCall->setRelation('agent', $agent = new Agent());
        $agent->setRelation('user', $user = new User());
        $user->first_name = $firstName;
        $ticket           = Mockery::mock(Ticket::class);
        $ticket->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'TICKET_ID');

        $notification = new UnsolvedTicketNotification($agentCall, $ticket);

        $fcmMessage = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $data = [
            'type'   => 'source',
            'source' => json_encode([
                'event' => UnsolvedTicketNotification::SOURCE_EVENT,
                'type'  => UnsolvedTicketNotification::SOURCE_TYPE,
                'id'    => $id,
            ]),
        ];

        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $this->assertSame("Did {$firstName} solve your problem?", $fcmNotification->getTitle());
        $this->assertSame("Tap here to leave feedback about your experience with {$firstName}!",
            $fcmNotification->getBody());
    }
}
