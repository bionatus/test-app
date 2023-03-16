<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Events\Order\Assigned as AssignedEvent;
use App\Listeners\Order\SendAssignSmsNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\AssignSmsNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use Notification;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendAssignSmsNotificationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendAssignSmsNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_notification_to_a_technician_when_user_exist()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();
        $supplierNotificationSetting = Mockery::mock(GetNotificationSetting::class);
        $supplierNotificationSetting->shouldReceive('execute')->withNoArgs()->andReturnFalse();
        App::bind(GetNotificationSetting::class, fn() => $supplierNotificationSetting);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_SMS,
            'value' => true,
        ]);

        $event    = new AssignedEvent($order);
        $listener = App::make(SendAssignSmsNotification::class);
        $listener->handle($event);

        Notification::assertSentTo($user, AssignSmsNotification::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_does_not_send_a_notification_to_a_technician_when_user_not_exist()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();
        $supplierNotificationSetting = Mockery::mock(GetNotificationSetting::class);
        $supplierNotificationSetting->shouldReceive('execute')->withNoArgs()->andReturnFalse();
        App::bind(GetNotificationSetting::class, fn() => $supplierNotificationSetting);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create(['user_id' => null]);

        $event    = new AssignedEvent($order);
        $listener = App::make(SendAssignSmsNotification::class);
        $listener->handle($event);

        Notification::assertSentTimes(AssignSmsNotification::class, 0);
    }
}
