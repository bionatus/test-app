<?php

namespace Tests\Unit\Listeners\Order\Delivery;

use App\Events\Order\DeliveryEtaUpdated;
use App\Listeners\Order\Delivery\SendOrderEtaUpdatedInAppNotification;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\OrderEtaUpdatedInAppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;
use Tests\TestCase;
use Throwable;

class SendOrderEtaUpdatedInAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendOrderEtaUpdatedInAppNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Throwable
     */
    public function it_sends_notifications_of_the_user_of_the_order()
    {
        Notification::fake();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);

        $event = new DeliveryEtaUpdated($order);

        $listener = new SendOrderEtaUpdatedInAppNotification();
        $listener->handle($event);

        Notification::assertSentTo($user, OrderEtaUpdatedInAppNotification::class);
    }
}
