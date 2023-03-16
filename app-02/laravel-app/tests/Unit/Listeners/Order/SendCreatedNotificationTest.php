<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Events\Order\Created as CreatedEvent;
use App\Listeners\Order\SendCreatedNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use App\Notifications\Supplier\OrderCreatedNotification as OrderCreatedNotificationToSupplier;
use App\Notifications\Supplier\Staff\OrderCreatedEmailNotification as OrderCreatedEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\OrderCreatedSmsNotification as OrderCreatedSmsNotificationToStaff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendCreatedNotificationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendCreatedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_sends_a_notification_to_a_supplier()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $event = new CreatedEvent($order);

        $listener = App::make(SendCreatedNotification::class);

        $listener->handle($event);

        Notification::assertSentTo($supplier, OrderCreatedNotificationToSupplier::class);
    }

    /** @test */
    public function it_sends_notifications_to_all_counters_staff_related_to_a_supplier()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $staffs   = Staff::factory()->usingSupplier($supplier)->counter(3)->create();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_SMS,
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

        $event = new CreatedEvent($order);

        $listener = App::make(SendCreatedNotification::class);

        $listener->handle($event);

        $staffs->each(function($staff) {
            Notification::assertSentTo($staff, OrderCreatedEmailNotificationToStaff::class);
            Notification::assertSentTo($staff, OrderCreatedSmsNotificationToStaff::class);
        });
    }
}
