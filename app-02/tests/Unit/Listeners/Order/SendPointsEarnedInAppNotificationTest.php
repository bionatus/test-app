<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Events\Order\PointsEarned;
use App\Listeners\Order\SendPointsEarnedInAppNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\OrderPointsEarnedInAppNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use ReflectionClass;
use Tests\TestCase;

class SendPointsEarnedInAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendPointsEarnedInAppNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_notification_to_a_technician_when_user_exists()
    {
        Notification::fake();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_BLUON_POINTS_EARNED_IN_APP,
            'value' => true,
        ]);

        $event    = new PointsEarned($order);
        $listener = App::make(SendPointsEarnedInAppNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, OrderPointsEarnedInAppNotification::class);
    }

    /** @test */
    public function it_does_not_send_a_notification_to_a_technician_when_user_not_exist()
    {
        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create(['user_id' => null]);

        $event    = new PointsEarned($order);
        $listener = App::make(SendPointsEarnedInAppNotification::class);
        $listener->handle($event);

        Notification::assertSentTimes(OrderPointsEarnedInAppNotification::class, 0);
    }
}
