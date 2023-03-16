<?php

namespace Tests\Unit\Notifications\Agent;

use App\Models\Session;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\Agent\TechCallingNotification;
use App\Notifications\SendsPushNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Fcm\FcmMessage;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class TechCallingNotificationTest extends TestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(TechCallingNotification::class, SendsPushNotification::class);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(TechCallingNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        $session = new Session();
        $session->setRelation('user', $user = User::factory()->make());
        $session->setRelation('subject', $subject = Subject::factory()->topic()->make());

        $notification = new TechCallingNotification($session);

        $fcmMessage = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $data = [
            'type'     => 'resource',
            'resource' => json_encode([
                'type' => 'tech_calling',
                'data' => [
                    'user'  => [
                        'id'    => $user->getKey(),
                        'name'  => $user->name,
                        'photo' => $user->photo,
                    ],
                    'topic' => [
                        'id'   => $subject->getRouteKey(),
                        'name' => $subject->name,
                    ],
                ],
            ]),
        ];

        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());
    }
}
