<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Events\Order\PointsEarned;
use App\Listeners\Order\SendPointsEarnedSmsNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\OrderPointsEarnedSmsNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use ReflectionClass;
use Tests\TestCase;

class SendPointsEarnedSmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendPointsEarnedSmsNotification::class);

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
            'slug'  => Setting::SLUG_BLUON_POINTS_EARNED_SMS,
            'value' => true,
        ]);

        $event    = new PointsEarned($order);
        $listener = App::make(SendPointsEarnedSmsNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, OrderPointsEarnedSmsNotification::class);
    }

    /** @test */
    public function it_does_not_send_a_notification_to_a_technician_when_user_not_exist()
    {
        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create(['user_id' => null]);

        $event    = new PointsEarned($order);
        $listener = App::make(SendPointsEarnedSmsNotification::class);
        $listener->handle($event);

        Notification::assertSentTimes(OrderPointsEarnedSmsNotification::class, 0);
    }
}
