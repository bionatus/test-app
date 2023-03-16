<?php

namespace Tests\Unit\Notifications;

use App\Models\InternalNotification;
use App\Models\Post;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\User;
use App\Notifications\PostSolvedNotification;
use App\Notifications\SendsPushNotification;
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

class PostSolvedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(PostSolvedNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(PostSolvedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $post         = Post::factory()->create();
        $notification = new PostSolvedNotification($post);

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
        $post         = Post::factory()->usingUser($user)->create();
        $notification = new PostSolvedNotification($post);

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

        $this->assertSame($expected, in_array(FcmChannel::class, $notification->via($user)));
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

        $user = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $post = Post::factory()->create();

        Setting::factory()->create([
            'slug'  => Setting::SLUG_DISABLE_FORUM_NOTIFICATION,
            'value' => true,
        ]);

        (new PostSolvedNotification($post))->via($user);

        $internalNotification = [
            'user_id'      => $user->getKey(),
            'message'      => 'A post with your followed tag has been solved.',
            'source_event' => 'solved',
            'source_type'  => 'post',
            'source_id'    => $post->getRouteKey(),
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

        $user = User::factory()->create();
        $post = Post::factory()->usingUser($user)->create();

        Setting::factory()->create([
            'slug'  => Setting::SLUG_DISABLE_FORUM_NOTIFICATION,
            'value' => false,
        ]);
        Setting::factory()->create([
            'slug'  => Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED,
            'value' => true,
        ]);

        $notification = new PostSolvedNotification($post);
        $notification->via($user);
        $fcmMessage = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $property = new ReflectionProperty($notification, 'internalNotification');
        $property->setAccessible(true);

        $data = [
            'source' => json_encode([
                'event'                    => 'solved',
                'type'                     => 'post',
                'id'                       => $post->getRouteKey(),
                'internal_notification_id' => $property->getValue($notification)->getRouteKey(),
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $body            = 'A post with your followed tag has been solved.';
        $title           = 'Post solved';

        $this->assertEquals($body, $fcmNotification->getBody());
        $this->assertEquals($title, $fcmNotification->getTitle());
    }
}
