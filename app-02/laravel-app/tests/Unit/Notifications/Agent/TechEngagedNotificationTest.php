<?php

namespace Tests\Unit\Notifications\Agent;

use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Models\Ticket;
use App\Notifications\Agent\TechEngagedNotification;
use App\Notifications\SendsPushNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Fcm\FcmMessage;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class TechEngagedNotificationTest extends TestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(TechEngagedNotification::class, SendsPushNotification::class);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(TechEngagedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        $agentCall = new AgentCall();
        $agentCall->setRelation('call', $call = new Call());
        $call->setAttribute('communication', $communication = new Communication());
        $communication->setRelation('session', $session = new Session());
        $session->setRelation('ticket', $ticket = new Ticket());

        $notification = new TechEngagedNotification($agentCall);

        $fcmMessage = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $data = [
            'type'     => 'resource',
            'resource' => json_encode([
                'type' => 'tech_engaged',
                'data' => [
                    'ticket' => [
                        'id' => $ticket->getRouteKey(),
                    ],
                ],
            ]),
        ];

        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());
    }
}
