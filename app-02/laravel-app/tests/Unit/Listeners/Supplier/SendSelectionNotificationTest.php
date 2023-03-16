<?php

namespace Tests\Unit\Listeners\Supplier;

use App;
use App\Events\Supplier\Selected as SelectedEvent;
use App\Listeners\Supplier\SendSelectionNotification;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use App\Notifications\Supplier\SelectionNotification as SelectionNotificationToSupplier;
use App\Notifications\Supplier\Staff\SelectionEmailNotification as SelectionEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\SelectionSmsNotification as SelectionSmsNotificationToStaff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendSelectionNotificationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendSelectionNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_sends_a_notification_to_a_supplier()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $event = new SelectedEvent($supplier);

        $listener = App::make(SendSelectionNotification::class);

        $listener->handle($event);

        Notification::assertSentTo($supplier, SelectionNotificationToSupplier::class);
    }

    /** @test */
    public function it_sends_notifications_to_all_counters_staff_related_to_a_supplier()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $staffs   = Staff::factory()->usingSupplier($supplier)->counter(3)->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->boolean()->create([
            'slug'  => Setting::SLUG_STAFF_SMS_NOTIFICATION,
            'value' => false,
        ]);
        Setting::factory()->groupNotification()->boolean()->create([
            'slug'  => Setting::SLUG_STAFF_EMAIL_NOTIFICATION,
            'value' => false,
        ]);

        $event = new SelectedEvent($supplier);

        $listener = App::make(SendSelectionNotification::class);

        $listener->handle($event);

        $staffs->each(function($staff) {
            Notification::assertSentTo($staff, SelectionEmailNotificationToStaff::class);
            Notification::assertSentTo($staff, SelectionSmsNotificationToStaff::class);
        });
    }
}
