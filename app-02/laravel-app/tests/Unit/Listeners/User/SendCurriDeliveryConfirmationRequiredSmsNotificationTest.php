<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Events\Order\Delivery\Curri\UserConfirmationRequired;
use App\Listeners\User\SendCurriDeliveryConfirmationRequiredSmsNotification;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\CurriDeliveryConfirmationRequiredSmsNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Notification;
use ReflectionClass;
use Tests\TestCase;

class SendCurriDeliveryConfirmationRequiredSmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendCurriDeliveryConfirmationRequiredSmsNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_sms_notification_to_a_technician()
    {
        Notification::fake();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();

        $event    = new UserConfirmationRequired($order);
        $listener = App::make(SendCurriDeliveryConfirmationRequiredSmsNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, CurriDeliveryConfirmationRequiredSmsNotification::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_does_not_send_a_sms_notification_to_a_technician_when_user_not_exist()
    {
        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create(['user_id' => null]);

        $event    = new UserConfirmationRequired($order);
        $listener = App::make(SendCurriDeliveryConfirmationRequiredSmsNotification::class);
        $listener->handle($event);

        Notification::assertSentTimes(UserConfirmationRequired::class, 0);
    }
}
