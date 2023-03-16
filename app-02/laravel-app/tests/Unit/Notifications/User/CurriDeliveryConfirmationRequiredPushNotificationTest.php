<?php

namespace Tests\Unit\Notifications\User;

use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\CurriDeliveryConfirmationRequiredPushNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class CurriDeliveryConfirmationRequiredPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(CurriDeliveryConfirmationRequiredPushNotification::class, SendsPushNotification::class,
            ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CurriDeliveryConfirmationRequiredPushNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->create();
        $notification = new CurriDeliveryConfirmationRequiredPushNotification($order);

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
        $notification = new CurriDeliveryConfirmationRequiredPushNotification($order);

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

    /** @test */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create(['name' => 'order name']);

        $notification = new CurriDeliveryConfirmationRequiredPushNotification($order);
        $fcmMessage   = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $data = [
            'source' => json_encode([
                'event' => 'confirm_delivery',
                'type'  => 'order',
                'id'    => $order->getRouteKey(),
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();
        $body            = "PO {$order->name} is ready to be picked up and delivered. Please confirm your delivery details.";

        $this->assertEquals($body, $fcmNotification->getBody());
        $this->assertEquals('Confirm Your Delivery', $fcmNotification->getTitle());
    }
}
