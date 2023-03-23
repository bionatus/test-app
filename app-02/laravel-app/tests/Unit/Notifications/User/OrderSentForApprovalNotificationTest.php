<?php

namespace Tests\Unit\Notifications\User;

use App\Models\AppSetting;
use App\Models\InternalNotification;
use App\Models\ItemOrder;
use App\Models\Level;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Phone;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\OrderSentForApprovalNotification;
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

class OrderSentForApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    private float $coefficient;
    private int   $multiplier;

    public function setUp(): void
    {
        parent::setUp();

        Level::factory()->create([
            'slug'        => Level::SLUG_LEVEL_0,
            'coefficient' => $coefficient = 1,
        ]);
        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => $multiplier = 1,
        ]);

        $this->coefficient = $coefficient;
        $this->multiplier  = $multiplier;
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(OrderSentForApprovalNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderSentForApprovalNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        $notification = new OrderSentForApprovalNotification($order);

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
        $order        = Order::factory()->usingSupplier($supplier)->usingUser($user)->pendingApproval()->create();
        $notification = new OrderSentForApprovalNotification($order);

        $setting = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_IN_APP,
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
        $order        = Order::factory()->usingSupplier($supplier)->usingUser($user)->pendingApproval()->create();
        $notification = new OrderSentForApprovalNotification($order);

        $setting = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_SMS,
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
        ItemOrder::factory()->usingOrder($order)->count($quantity = 3)->available()->create([
            'price'    => $price = 555,
            'quantity' => 1,
        ]);
        $pointsEarned = (int) ceil($quantity * $price * $this->coefficient * $this->multiplier);

        new OrderSentForApprovalNotification($order);

        $internalNotification = [
            'message'      => "You just received a quote from $supplierName. Get $pointsEarned Points once approved.",
            'source_event' => 'sent_for_approval',
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
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => $date = Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        ItemOrder::factory()->usingOrder($order)->count($quantity = 3)->available()->create([
            'price'    => $price = 555,
            'quantity' => 1,
        ]);
        $pointsEarned = (int) ceil($quantity * $price * $this->coefficient * $this->multiplier);

        $toTwilio = (new OrderSentForApprovalNotification($order))->toTwilio(null);

        $availability = $date->format('m-d-Y') . ' ' . $order->orderDelivery->time_range;
        $smsMessage   = "Bluon - $supplierName sent a quote. Get $pointsEarned Points once approved for 2% cash back!\nOrder ready on $availability.\nDo not reply.";
        $expected     = (new TwilioSmsMessage())->content($smsMessage);

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
        ItemOrder::factory()->usingOrder($order)->count($quantity = 3)->available()->create([
            'price'    => $price = 555,
            'quantity' => 1,
        ]);
        $pointsEarned = (int) ceil($quantity * $price * $this->coefficient * $this->multiplier);

        $notification = new OrderSentForApprovalNotification($order);
        $fcmMessage   = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $property = new ReflectionProperty($notification, 'internalNotification');
        $property->setAccessible(true);

        $data = [
            'source' => json_encode([
                'event'                    => 'sent_for_approval',
                'type'                     => 'order',
                'id'                       => $order->getRouteKey(),
                'internal_notification_id' => $property->getValue($notification)->getRouteKey(),
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $body            = "You just received a quote from $supplierName. Get $pointsEarned Points once approved.";
        $title           = 'New Quote';

        $this->assertEquals($body, $fcmNotification->getBody());
        $this->assertEquals($title, $fcmNotification->getTitle());
    }
}
