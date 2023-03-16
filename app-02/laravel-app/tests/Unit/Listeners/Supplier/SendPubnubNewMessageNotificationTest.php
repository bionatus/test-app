<?php

namespace Tests\Unit\Listeners\Supplier;

use App;
use App\Events\Supplier\NewMessage;
use App\Listeners\Supplier\SendPubnubNewMessageNotification;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\Supplier\PubnubNewMessageNotification as PubnubNewMessageNotificationToSupplier;
use App\Notifications\Supplier\Staff\PubnubNewMessageEmailNotification as PubnubNewMessageEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\PubnubNewMessageSmsNotification as PubnubNewMessageSmsNotificationToStaff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendPubnubNewMessageNotificationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendPubnubNewMessageNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_sends_a_notification_to_a_supplier()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $event = new NewMessage($supplier, $user, 'Test Message');

        $listener = App::make(SendPubnubNewMessageNotification::class);

        $listener->handle($event);

        Notification::assertSentTo($supplier, PubnubNewMessageNotificationToSupplier::class);
    }

    /** @test */
    public function it_sends_notifications_to_all_counters_staff_related_to_a_supplier()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $supplier = Supplier::factory()->createQuietly();
        $staffs   = Staff::factory()->usingSupplier($supplier)->counter(3)->create();
        $user     = User::factory()->create();

        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS,
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

        $event = new NewMessage($supplier, $user, 'Test Message');

        $listener = App::make(SendPubnubNewMessageNotification::class);

        $listener->handle($event);

        $staffs->each(function($staff) {
            Notification::assertSentTo($staff, PubnubNewMessageEmailNotificationToStaff::class);
            Notification::assertSentTo($staff, PubnubNewMessageSmsNotificationToStaff::class);
        });
    }
}
