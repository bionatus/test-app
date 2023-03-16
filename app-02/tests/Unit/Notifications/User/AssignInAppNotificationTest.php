<?php

namespace Tests\Unit\Notifications\User;

use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\AssignInAppNotification;
use Config;
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

class AssignInAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(AssignInAppNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(AssignInAppNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->create();
        $notification = new AssignInAppNotification($order);

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider viaDataProvider
     */
    public function it_is_sent_via_fcm_if_requirements_are_met(
        bool $expected,
        bool $configValue,
        bool $settingValue,
        bool $enabled
    ) {
        Config::set('notifications.push.enabled', $configValue);
        $user         = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->usingUser($user)->create();
        $notification = new AssignInAppNotification($order);

        $setting = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_IN_APP,
            'value' => true,
        ]);

        SettingUser::factory()->usingSetting($setting)->usingUser($user)->create(['value' => $settingValue]);

        $this->assertSame($expected, in_array(FcmChannel::class, $notification->via(null)));
    }

    public function viaDataProvider(): array
    {
        return [
            [true, true, true, true],
            [false, true, true, false],
            [false, true, false, true],
            [false, true, false, false],
            [false, false, true, true],
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

        $user     = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName = 'Fake name']);
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->create();

        new AssignInAppNotification($order);

        $internalNotification = [
            'message'                 => "Your have unread messages from $supplierName. Tap here to view messages!",
            'source_event'            => 'new-message',
            'source_type'             => 'supplier',
            'source_id'               => $supplier->getRouteKey(),
            'data->channel_id'        => "supplier-{$supplier->getRouteKey()}.user-{$user->getRouteKey()}",
            'data->supplier_data->id' => $supplier->getRouteKey(),
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

        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName = 'Fake name']);
        $order    = Order::factory()->usingSupplier($supplier)->create([
            'working_on_it' => $workingOnIt = 'Fake name',
        ]);

        $notification = new AssignInAppNotification($order);
        $fcmMessage   = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $property = new ReflectionProperty($notification, 'internalNotification');
        $property->setAccessible(true);

        $data = [
            'source' => json_encode([
                'event'                    => 'assigned',
                'type'                     => 'order',
                'id'                       => $order->getRouteKey(),
                'internal_notification_id' => $property->getValue($notification)->getRouteKey(),
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $body            = "$workingOnIt is working on your quote. Stay tuned!";

        $this->assertEquals($body, $fcmNotification->getBody());
        $this->assertEquals($supplierName, $fcmNotification->getTitle());
    }
}
