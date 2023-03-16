<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Events\Order\SentForApproval;
use App\Listeners\Order\SendSentForApprovalNotification;
use App\Models\AppSetting;
use App\Models\Level;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\OrderSentForApprovalNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use ReflectionClass;
use Tests\TestCase;

class SendSentForApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendSentForApprovalNotification::class);

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
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_IS_READY_FOR_APPROVAL_IN_APP,
            'value' => true,
        ]);
        Level::factory()->create([
            'slug'        => Level::SLUG_LEVEL_0,
            'coefficient' => 1,
        ]);
        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 1,
        ]);

        $event    = new SentForApproval($order);
        $listener = App::make(SendSentForApprovalNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, OrderSentForApprovalNotification::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_does_not_send_a_notification_to_a_technician_when_user_not_exist()
    {
        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create(['user_id' => null]);

        $event    = new SentForApproval($order);
        $listener = App::make(SendSentForApprovalNotification::class);
        $listener->handle($event);

        Notification::assertSentTimes(OrderSentForApprovalNotification::class, 0);
    }
}
