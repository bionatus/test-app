<?php

namespace Tests\Unit\Notifications;

use App\Models\Comment;
use App\Models\InternalNotification;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\SolutionCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Tests\TestCase;

class SolutionCreatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(SolutionCreatedNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SolutionCreatedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $comment      = Comment::factory()->create();
        $notification = new SolutionCreatedNotification($comment);

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider viaDataProvider
     */
    public function it_is_sent_via_fcm_if_requirements_are_met(
        bool $expected,
        bool $forumValue,
        bool $settingValue,
        bool $enabled
    ) {
        $user         = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $comment      = Comment::factory()->usingUser($user)->create();
        $notification = new SolutionCreatedNotification($comment);

        $forumSetting = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_DISABLE_FORUM_NOTIFICATION,
            'value' => false,
        ]);
        $setting      = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED,
            'value' => true,
        ]);

        SettingUser::factory()->usingSetting($forumSetting)->usingUser($user)->create(['value' => $forumValue]);
        SettingUser::factory()->usingSetting($setting)->usingUser($user)->create(['value' => $settingValue]);

        $this->assertSame($expected, in_array(FcmChannel::class, $notification->via(null)));
    }

    public function viaDataProvider(): array
    {
        return [
            [false, true, true, true],
            [false, true, true, false],
            [false, true, false, true],
            [false, true, false, false],
            [true, false, true, true],
            [false, false, true, false],
            [false, false, false, true],
            [false, false, false, false],
        ];
    }

    /** @test
     * @dataProvider internalNotificationDataProvider
     */
    public function it_creates_an_internal_notification_if_requirements_are_met($enabled)
    {
        Notification::fake();

        $user    = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $comment = Comment::factory()->usingUser($user)->create();

        new SolutionCreatedNotification($comment);

        $internalNotification = [
            'message'      => 'Your response was badass and marked the “Best Answer”.',
            'source_event' => 'selected',
            'source_type'  => 'comment',
            'source_id'    => $comment->post->getRouteKey(),
        ];

        if ($enabled) {
            $this->assertDatabaseHas(InternalNotification::tableName(), $internalNotification);
        } else {
            $this->assertDatabaseMissing(InternalNotification::tableName(), $internalNotification);
        }
    }

    public function internalNotificationDataProvider(): array
    {
        return [[true], [false]];
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        Notification::fake();

        $comment = Comment::factory()->create();

        $notification = new SolutionCreatedNotification($comment);
        $fcmMessage   = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $property = new ReflectionProperty($notification, 'internalNotification');
        $property->setAccessible(true);

        $data = [
            'source' => json_encode([
                'event'                    => 'selected',
                'type'                     => 'comment',
                'id'                       => $comment->getRouteKey(),
                'post_id'                  => $comment->post->getRouteKey(),
                'internal_notification_id' => $property->getValue($notification)->getRouteKey(),
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $body            = 'Your response was badass and marked the “Best Answer”.';
        $title           = 'Post solved';

        $this->assertEquals($body, $fcmNotification->getBody());
        $this->assertEquals($title, $fcmNotification->getTitle());
    }
}
