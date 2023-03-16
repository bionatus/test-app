<?php

namespace Tests\Unit\Notifications\Tech;

use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Models\Subject;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\Tech\AgentAnsweredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Fcm\FcmMessage;
use ReflectionClass;
use ReflectionException;
use Storage;
use Tests\TestCase;

class AgentAnsweredNotificationTest extends TestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(AgentAnsweredNotification::class, SendsPushNotification::class);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(AgentAnsweredNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        $agentCall = new AgentCall();
        $agentCall->setRelation('call', $call = new Call());
        $agentCall->setRelation('agent', $agent = new User());
        $agent->setRelation('user', $user = new User());
        $call->setAttribute('communication', $communication = new Communication());
        $communication->setRelation('session', $session = new Session());
        $session->setRelation('subject', $subject = new Subject());
        $session->setRelation('ticket', $ticket = new Ticket());

        $notification = new AgentAnsweredNotification($agentCall);

        $fcmMessage = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $data = [
            'type'     => 'resource',
            'resource' => json_encode([
                'type' => 'agent_answered',
                'data' => [
                    'user'   => [
                        'id'         => $user->getRouteKey(),
                        'name'       => $user->name ?: ($user->first_name . ' ' . $user->last_name),
                        'experience' => $user->experience_years,
                        'photo'      => !empty($user->photo) ? asset(Storage::url($user->photo)) : null,
                    ],
                    'topic'  => [
                        'id'   => $subject->getRouteKey(),
                        'name' => $subject->name,
                    ],
                    'ticket' => [
                        'id' => $ticket->getRouteKey(),
                    ],
                ],
            ]),
        ];

        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());
    }
}
