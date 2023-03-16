<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Events\Order\Declined;
use App\Listeners\Order\SendDeclinedNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\OrderDeclinedNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use ReflectionClass;
use Tests\TestCase;

class SendDeclinedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendDeclinedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_notification_to_a_technician_when_user_exist()
    {
        Notification::fake();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_IS_CANCELED_IN_APP,
            'value' => true,
        ]);

        $event    = new Declined($order);
        $listener = App::make(SendDeclinedNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, OrderDeclinedNotification::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_does_not_send_a_notification_to_a_technician_when_user_not_exist()
    {
        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create(['user_id' => null]);

        $event    = new Declined($order);
        $listener = App::make(SendDeclinedNotification::class);
        $listener->handle($event);

        Notification::assertSentTimes(OrderDeclinedNotification::class, 0);
    }
}
