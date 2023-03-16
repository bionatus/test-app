<?php

namespace Tests\Unit\Listeners\Supplier;

use App;
use App\Events\Order\Created;
use App\Listeners\Supplier\LogOrderIntoMissedOrderRequest;
use App\Models\MissedOrderRequest;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\SupplierUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class LogOrderIntoMissedOrderRequestTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(LogOrderIntoMissedOrderRequest::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new LogOrderIntoMissedOrderRequest();

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_logs_the_user_suppliers_selected_that_did_not_get_the_order()
    {
        $this->refreshDatabaseForSingleTest();
        $order              = Order::factory()->createQuietly();
        $otherSupplierUsers = SupplierUser::factory()->usingUser($order->user)->count(5)->createQuietly();
        SupplierUser::factory()->usingUser($order->user)->usingSupplier($order->supplier)->createQuietly();

        $event = new Created($order);

        $listener = App::make(LogOrderIntoMissedOrderRequest::class);
        $listener->handle($event);

        $this->assertDatabaseMissing(MissedOrderRequest::tableName(), [
            'order_id'           => $order->getKey(),
            'missed_supplier_id' => $order->supplier_id,
            'created_at'         => $order->created_at,
        ]);

        $otherSupplierUsers->each(function(SupplierUser $supplierUser) use ($order) {
            $this->assertDatabaseHas(MissedOrderRequest::tableName(), [
                'order_id'           => $order->getKey(),
                'missed_supplier_id' => $supplierUser->supplier_id,
                'created_at'         => $order->created_at,
            ]);
        });
    }

    /** @test */
    public function it_doest_not_log_suppliers_if_the_user_only_have_selected_the_supplier_of_the_order()
    {
        $this->refreshDatabaseForSingleTest();
        $order = Order::factory()->createQuietly();
        SupplierUser::factory()->createQuietly();
        SupplierUser::factory()->usingUser($order->user)->usingSupplier($order->supplier)->createQuietly();

        $event = new Created($order);

        $listener = App::make(LogOrderIntoMissedOrderRequest::class);
        $listener->handle($event);

        $this->assertDatabaseMissing(MissedOrderRequest::tableName(), [
            'order_id'           => $order->getKey(),
            'missed_supplier_id' => $order->supplier_id,
            'created_at'         => $order->created_at,
        ]);
    }

    /** @test */
    public function it_does_not_log_suppliers_if_the_relationship_it_is_not_visible()
    {
        $this->refreshDatabaseForSingleTest();
        $supplier                = Supplier::factory()->createQuietly();
        $order                   = Order::factory()->usingSupplier($supplier)->create();
        $notVisibleSupplierUsers = SupplierUser::factory()
            ->usingUser($order->user)
            ->count(2)
            ->notVisible()
            ->createQuietly();

        $event = new Created($order);

        $listener = App::make(LogOrderIntoMissedOrderRequest::class);
        $listener->handle($event);

        $notVisibleSupplierUsers->each(function(SupplierUser $supplierUser) use ($order) {
            $this->assertDatabaseMissing(MissedOrderRequest::tableName(), [
                'order_id'           => $order->getKey(),
                'missed_supplier_id' => $supplierUser->supplier_id,
                'created_at'         => $order->created_at,
            ]);
        });
    }
}
