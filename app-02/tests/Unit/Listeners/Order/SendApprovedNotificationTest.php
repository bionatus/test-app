<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Actions\Models\SettingUser\GetNotificationSetting as GetNotificationSettingUser;
use App\Events\Order\LegacyApproved as ApprovedEvent;
use App\Listeners\Order\SendApprovedNotification;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use App\Notifications\Supplier\OrderApprovedNotification as OrderApprovedNotificationToSupplier;
use App\Notifications\Supplier\Staff\OrderApprovedEmailNotification as OrderApprovedEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\OrderApprovedSmsNotification as OrderApprovedSmsNotificationToStaff;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use Notification;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendApprovedNotificationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendApprovedNotification::class);

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
            'slug'  => Setting::SLUG_ORDER_APPROVED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_APPROVED_NOTIFICATION_SMS,
            'value' => true,
        ]);

        $userNotificationSetting = Mockery::mock(GetNotificationSettingUser::class);
        $userNotificationSetting->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSettingUser::class, fn() => $userNotificationSetting);

        $event = new ApprovedEvent($order);

        $listener = App::make(SendApprovedNotification::class);

        $listener->handle($event);

        Notification::assertSentTo($supplier, OrderApprovedNotificationToSupplier::class);
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
            'slug'  => Setting::SLUG_ORDER_APPROVED_NOTIFICATION_EMAIL,
            'value' => true,
        ]);
        Setting::factory()->groupNotification()->applicableToSupplier()->create([
            'slug'  => Setting::SLUG_ORDER_APPROVED_NOTIFICATION_SMS,
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

        $userNotificationSetting = Mockery::mock(GetNotificationSettingUser::class);
        $userNotificationSetting->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSettingUser::class, fn() => $userNotificationSetting);

        $event = new ApprovedEvent($order);

        $listener = App::make(SendApprovedNotification::class);

        $listener->handle($event);

        $staffs->each(function($staff) {
            Notification::assertSentTo($staff, OrderApprovedEmailNotificationToStaff::class);
            Notification::assertSentTo($staff, OrderApprovedSmsNotificationToStaff::class);
        });
    }
}
