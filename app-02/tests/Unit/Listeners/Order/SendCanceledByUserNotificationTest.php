<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Events\Order\CanceledByUser;
use App\Listeners\Order\SendCanceledByUserNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use App\Notifications\Supplier\OrderCanceledByUserNotification as OrderCanceledByUserNotificationToSupplier;
use App\Notifications\Supplier\Staff\OrderCanceledByUserEmailNotification as OrderCanceledByUserEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\OrderCanceledByUserSmsNotification as OrderCanceledByUserSmsNotificationToStaff;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendCanceledByUserNotificationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendCanceledByUserNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_notification_to_a_supplier()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $event = new CanceledByUser($order);

        $listener = App::make(SendCanceledByUserNotification::class);

        $listener->handle($event);

        Notification::assertSentTo($supplier, OrderCanceledByUserNotificationToSupplier::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_notifications_to_all_counters_staff_related_to_a_supplier()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $staffs   = Staff::factory()->usingSupplier($supplier)->counter(3)->create();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToStaff()->boolean()->create([
            'slug'  => Setting::SLUG_STAFF_SMS_NOTIFICATION,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->applicableToStaff()->boolean()->create([
            'slug'  => Setting::SLUG_STAFF_EMAIL_NOTIFICATION,
            'value' => false,
        ]);

        $event = new CanceledByUser($order);

        $listener = App::make(SendCanceledByUserNotification::class);

        $listener->handle($event);

        $staffs->each(function($staff) {
            Notification::assertSentTo($staff, OrderCanceledByUserEmailNotificationToStaff::class);
            Notification::assertSentTo($staff, OrderCanceledByUserSmsNotificationToStaff::class);
        });
    }
}
