<?php

namespace Tests\Unit\Notifications\User;

use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\CurriDeliveryArrivedAtDestinationInAppNotification;
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

class CurriDeliveryArrivedAtDestinationInAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(CurriDeliveryArrivedAtDestinationInAppNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CurriDeliveryArrivedAtDestinationInAppNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->create();
        $notification = new CurriDeliveryArrivedAtDestinationInAppNotification($order);

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider viaDataProvider
     */
    public function it_is_sent_via_fcm_if_requirements_are_met(
        bool $expected,
        bool $configValue,
        bool $enabled
    ) {
        Config::set('notifications.push.enabled', $configValue);
        $user         = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->usingUser($user)->create();
        $notification = new CurriDeliveryArrivedAtDestinationInAppNotification($order);

        $this->assertSame($expected, in_array(FcmChannel::class, $notification->via(null)));
    }

    public function viaDataProvider(): array
    {
        return [
            [true, true, true],
            [false, true, false],
            [false, false, true],
            [false, false, false],
        ];
    }

    /** @test
     * @dataProvider internalNotificationDataProvider
     */
    public function it_creates_an_internal_notification_if_requirements_are_met($enabled)
    {
        Notification::fake();

        $user     = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->create(['name' => $name = 'order name']);

        new CurriDeliveryArrivedAtDestinationInAppNotification($order);

        $internalNotification = [
            'message'      => "Your Order PO $name has arrived.",
            'source_event' => 'at_destination',
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

    /** @test
     * @throws ReflectionException
     */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        Notification::fake();

        $title    = 'Your Order is here';
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create([
            'name' => $name = 'Fake name',
        ]);

        $notification = new CurriDeliveryArrivedAtDestinationInAppNotification($order);
        $fcmMessage   = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $property = new ReflectionProperty($notification, 'internalNotification');
        $property->setAccessible(true);

        $data = [
            'source' => json_encode([
                'event'                    => 'at_destination',
                'type'                     => 'order',
                'id'                       => $order->getRouteKey(),
                'internal_notification_id' => $property->getValue($notification)->getRouteKey(),
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $body            = "Your Order PO $name has arrived.";

        $this->assertEquals($body, $fcmNotification->getBody());
        $this->assertEquals($title, $fcmNotification->getTitle());
    }
}
