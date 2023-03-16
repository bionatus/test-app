<?php

namespace Tests\Unit\Notifications\User;

use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\OrderEtaUpdatedInAppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Notification;
use ReflectionClass;
use Tests\TestCase;

class OrderEtaUpdatedInAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderEtaUpdatedInAppNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['date' => Carbon::now()]);
        $notification = new OrderEtaUpdatedInAppNotification($order);

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider internalNotificationDataProvider
     */
    public function it_creates_an_internal_notification_if_requirements_are_met($enabled)
    {
        Notification::fake();

        $user          = User::factory()->create(['disabled_at' => $enabled ? null : Carbon::now()]);
        $supplier      = Supplier::factory()->createQuietly(['name' => $supplierName = 'Fake name']);
        $order         = Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->create(['name' => $orderName = 'Fake order name']);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i') ,
            'end_time'   => Carbon::createFromTime(12)->format('H:i')
        ]);

        $date          = $orderDelivery->date->format('m-d-y');
        $time = $orderDelivery->time_range;
        new OrderEtaUpdatedInAppNotification($order);

        $internalNotification = [
            'message'      => "Your quote $orderName ETA has been updated by $supplierName. New ETA: $date, $time.",
            'source_event' => 'eta_updated',
            'source_type'  => 'order',
            'source_id'    => $order->getRouteKey(),
        ];
        if ($enabled) {
            $this->assertDatabaseHas(InternalNotification::tableName(), $internalNotification);
        } else {
            $this->assertDatabaseMissing(InternalNotification::tableName(), $internalNotification);
        }
    }

    public function internalNotificationDataProvider(): array
    {
        return [[true], [false]];
    }
}
