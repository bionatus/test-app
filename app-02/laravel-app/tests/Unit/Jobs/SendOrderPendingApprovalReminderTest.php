<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendOrderPendingApprovalReminder;
use App\Models\AppSetting;
use App\Models\Level;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Setting;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\OrderPendingApprovalInAppNotification;
use App\Notifications\User\OrderPendingApprovalSmsLinkNotification;
use App\Notifications\User\OrderPendingApprovalSmsNotification;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;
use Tests\TestCase;

class SendOrderPendingApprovalReminderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendOrderPendingApprovalReminder::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new SendOrderPendingApprovalReminder('a timezone');

        $this->assertEquals('database', $job->connection);
    }

    /** @test
     * @throws Exception
     */
    public function it_notifies_users_of_orders_on_fulfilled_or_quote_updated_status_for_more_than_one_hour_via_sms_and_push_notification(
    )
    {
        Notification::fake();

        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_ORDER_PENDING_APPROVAL_IN_APP,
            'value' => true,
        ]);
        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_ORDER_PENDING_APPROVAL_SMS,
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

        $now      = CarbonImmutable::now();
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create(['timezone' => $timezone = 'FooZone']);
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create(['created_at' => $now->subMinutes(61)]);
        OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->count(3)
            ->createQuietly();

        $job = new SendOrderPendingApprovalReminder($timezone);
        $job->handle();

        Notification::assertSentTo($user, OrderPendingApprovalInAppNotification::class);
        Notification::assertSentTo($user, OrderPendingApprovalSmsNotification::class);
        Notification::assertSentTo($user, OrderPendingApprovalSmsLinkNotification::class);

        Notification::assertSentTimes(OrderPendingApprovalInAppNotification::class, 1);
        Notification::assertSentTimes(OrderPendingApprovalSmsNotification::class, 1);
        Notification::assertSentTimes(OrderPendingApprovalSmsLinkNotification::class, 1);
    }

    /** @test */
    public function it_does_not_sent_any_notification_if_the_has_not_user()
    {
        Notification::fake();

        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_ORDER_PENDING_APPROVAL_IN_APP,
            'value' => true,
        ]);
        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_ORDER_PENDING_APPROVAL_SMS,
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

        $now      = CarbonImmutable::now();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create(['user_id' => null]);
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create(['created_at' => $now->subMinutes(61)]);
        OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->count(5)
            ->createQuietly();

        $job = new SendOrderPendingApprovalReminder('FooZone');
        $job->handle();

        Notification::assertNothingSent();
    }
}
