<?php

namespace Tests\Unit\Notifications\User;

use App\Models\AppSetting;
use App\Models\InternalNotification;
use App\Models\ItemOrder;
use App\Models\Level;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\OrderPendingApprovalInAppNotification;
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

class OrderPendingApprovalInAppNotificationTest extends TestCase
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
        $this->assertUseTrait(OrderPendingApprovalInAppNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderPendingApprovalInAppNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create();

        $notification = new OrderPendingApprovalInAppNotification($order);

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
        $order        = Order::factory()->usingUser($user)->usingSupplier($supplier)->pendingApproval()->create();
        $notification = new OrderPendingApprovalInAppNotification($order);
        $setting      = Setting::factory()->applicableToUser()->create([
            'slug'  => Setting::SLUG_ORDER_PENDING_APPROVAL_IN_APP,
            'value' => true,
        ]);

        SettingUser::factory()->usingUser($user)->usingSetting($setting)->create(['value' => $settingValue]);

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
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->pendingApproval()->create();
        ItemOrder::factory()->usingOrder($order)->count($quantity = 3)->available()->create([
            'price'    => $price = 555,
            'quantity' => 1,
        ]);
        $pointsEarned = (int) ceil($quantity * $price * $this->coefficient * $this->multiplier);

        new OrderPendingApprovalInAppNotification($order);

        $message = "Quote alert! Approve quote to receive $pointsEarned points from $supplierName or decline. Tap here to view.";

        if ($enabled) {
            $this->assertDatabaseHas(InternalNotification::tableName(), [
                'message'      => $message,
                'source_event' => 'pending_approval',
                'source_type'  => 'order',
                'source_id'    => $order->getRouteKey(),
            ]);
        } else {
            $this->assertDatabaseMissing(InternalNotification::tableName(), [
                'message'      => $message,
                'source_event' => 'pending_approval',
                'source_type'  => 'order',
                'source_id'    => $order->getRouteKey(),
            ]);
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
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        ItemOrder::factory()->usingOrder($order)->count($quantity = 3)->available()->create([
            'price'    => $price = 555,
            'quantity' => 1,
        ]);
        $pointsEarned = (int) ceil($quantity * $price * $this->coefficient * $this->multiplier);

        $notification = new OrderPendingApprovalInAppNotification($order);
        $fcmMessage   = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $property = new ReflectionProperty($notification, 'internalNotification');
        $property->setAccessible(true);

        $data = [
            'source' => json_encode([
                'event'                    => 'pending_approval',
                'type'                     => 'order',
                'id'                       => $order->getRouteKey(),
                'internal_notification_id' => $property->getValue($notification)->getRouteKey(),
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $body            = "Quote Alert! Be sure to approve the Quote from $supplierName to receive $pointsEarned Points.";

        $this->assertEquals($body, $fcmNotification->getBody());
        $this->assertEmpty($fcmNotification->getTitle());
    }
}
