<?php

namespace Tests\Unit\Notifications\User;

use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\OrderPointsEarnedInAppNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Tests\TestCase;

class OrderPointsEarnedInAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    private $settingInApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingInApp = Setting::factory()
            ->applicableToUser()
            ->create(['slug' => Setting::SLUG_BLUON_POINTS_EARNED_IN_APP, 'value' => true]);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(OrderPointsEarnedInAppNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderPointsEarnedInAppNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->create();
        $notification = new OrderPointsEarnedInAppNotification($order);

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider pushNotificationsEnabledProvider
     */
    public function it_is_sent_via_fcm_if_requirements_are_met(bool $expected, bool $config, bool $setting)
    {
        Config::set('notifications.push.enabled', $config);
        $user         = User::factory()->create();
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $notification = new OrderPointsEarnedInAppNotification($order);

        SettingUser::factory()->usingUser($user)->usingSetting($this->settingInApp)->create(['value' => $setting]);

        $this->assertSame($expected, in_array(FcmChannel::class, $notification->via($user)));
    }

    public function pushNotificationsEnabledProvider(): array
    {
        return [
            [true, true, true],
            [false, true, false],
            [false, false, true],
            [false, false, false],
        ];
    }

    /** @test */
    public function it_creates_an_internal_notification_for_the_technician()
    {
        Notification::fake();

        $supplier        = Supplier::factory()->createQuietly();
        $name            = 'po number';
        $order           = Order::factory()->usingSupplier($supplier)->create(['name' => $name]);
        $user            = $order->user;
        $otherTechnician = User::factory()->create();

        $notification = new OrderPointsEarnedInAppNotification($order);
        $notification->via($user);

        $internalNotificationTable = InternalNotification::tableName();

        $message = "Way to go! By approving $name, you earned {$order->totalPointsEarned()} Points. You’ll lose them if you cancel.";
        $this->assertDatabaseHas($internalNotificationTable, [
            'message'      => $message,
            'source_event' => 'bluon_points_earned',
            'source_type'  => 'order',
            'source_id'    => $order->getRouteKey(),
            'user_id'      => $user->getKey(),
        ]);

        $this->assertDatabaseMissing($internalNotificationTable, [
            'user_id' => $otherTechnician->getKey(),
        ]);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $name     = 'po number';
        $order    = Order::factory()->usingSupplier($supplier)->create(['name' => $name]);
        $user     = $order->user;

        $notification = new OrderPointsEarnedInAppNotification($order);
        $notification->via($user);

        $fcmMessage = $notification->toFcm();

        $property = new ReflectionProperty($notification, 'internalNotification');
        $property->setAccessible(true);

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $data = [
            'source' => json_encode([
                'event'                    => 'bluon_points_earned',
                'type'                     => 'order',
                'id'                       => $order->getRouteKey(),
                'internal_notification_id' => $property->getValue($notification)->getRouteKey(),
            ]),
        ];

        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $body            = "Score! You just earned {$order->totalPointsEarned()} Points! for approving PO $name.";

        $this->assertEquals($body, $fcmNotification->getBody());
        $this->assertEquals('Points Earned!', $fcmNotification->getTitle());
    }
}
