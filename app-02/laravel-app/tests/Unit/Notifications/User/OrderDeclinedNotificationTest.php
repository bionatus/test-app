<?php

namespace Tests\Unit\Notifications\User;

use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\Phone;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\OrderDeclinedNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Tests\TestCase;

class OrderDeclinedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(OrderDeclinedNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderDeclinedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->create();
        $notification = new OrderDeclinedNotification($order);

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider fcmDataProvider
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
        $notification = new OrderDeclinedNotification($order);

        $setting = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_ORDER_IS_CANCELED_IN_APP,
            'value' => true,
        ]);

        SettingUser::factory()->usingSetting($setting)->usingUser($user)->create(['value' => $settingValue]);

        $this->assertSame($expected, in_array(FcmChannel::class, $notification->via(null)));
    }

    public function fcmDataProvider(): array
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
     * @dataProvider smsDataProvider
     */
    public function it_is_sent_via_sms_if_requirements_are_met(
        bool $expected,
        bool $configValue,
        bool $settingValue,
        bool $phone
    ) {
        Config::set('notifications.push.enabled', false);
        Config::set('notifications.sms.enabled', $configValue);
        $user = User::factory()->create();

        if ($phone) {
            Phone::factory()->usingUser($user)->create();
        }

        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->usingUser($user)->create();
        $notification = new OrderDeclinedNotification($order);

        $setting = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_ORDER_IS_CANCELED_SMS,
            'value' => true,
        ]);

        SettingUser::factory()->usingSetting($setting)->usingUser($user)->create(['value' => $settingValue]);

        $this->assertSame($expected, in_array(TwilioChannel::class, $notification->via(null)));
    }

    public function smsDataProvider(): array
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
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->pendingApproval()->create();

        new OrderDeclinedNotification($order);

        $internalNotification = [
            'message'      => "$supplierName has declined to give you a quote for your order request. Tap here to view!",
            'source_event' => 'declined',
            'source_type'  => 'order',
            'source_id'    => $order->getRouteKey(),
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

    /** @test */
    public function it_sets_twilio_message()
    {
        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName = 'Fake name']);
        $order    = Order::factory()->usingSupplier($supplier)->create();
        $toTwilio = (new OrderDeclinedNotification($order))->toTwilio(null);

        $smsMessage = "Bluon - $supplierName has declined to give you a quote for your order request. Do Not Reply to this text.";
        $expected   = (new TwilioSmsMessage())->content($smsMessage);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        Notification::fake();

        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName = 'Fake name']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        $notification = new OrderDeclinedNotification($order);
        $fcmMessage   = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $property = new ReflectionProperty($notification, 'internalNotification');
        $property->setAccessible(true);

        $data = [
            'source' => json_encode([
                'event'                    => 'declined',
                'type'                     => 'order',
                'id'                       => $order->getRouteKey(),
                'internal_notification_id' => $property->getValue($notification)->getRouteKey(),
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $body            = "$supplierName has declined to give you a quote for your order request. Tap here to view!";
        $title           = 'Order Declined';

        $this->assertEquals($body, $fcmNotification->getBody());
        $this->assertEquals($title, $fcmNotification->getTitle());
    }
}
