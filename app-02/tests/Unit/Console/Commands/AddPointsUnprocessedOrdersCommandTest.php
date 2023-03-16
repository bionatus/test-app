<?php

namespace Tests\Unit\Console\Commands;

use App;
use App\Actions\Models\Order\AddPoints;
use App\Console\Commands\AddPointsUnprocessedOrdersCommand;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Point;
use App\Models\Substatus;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AddPointsUnprocessedOrdersCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @param int  $substatusId
     * @param bool $expected
     *
     * @dataProvider dataProvider
     */
    public function it_execute_add_points_for_approved_or_completed_orders_without_previous_earned_points(
        int $substatusId,
        bool $expected
    ) {
        $supplier = Supplier::factory()->createQuietly();
        $orders   = Order::factory()->usingSupplier($supplier)->count(10)->create();
        $orders->each(function(Order $order) use ($substatusId) {
            OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        });

        $action = Mockery::mock(AddPoints::class);
        $action->shouldReceive('execute')->withAnyArgs()->times($expected ? 10 : 0);
        App::bind(AddPoints::class, fn() => $action);

        $command = new AddPointsUnprocessedOrdersCommand();
        $command->handle();
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, true],
            [Substatus::STATUS_APPROVED_DELIVERED, true],
            [Substatus::STATUS_COMPLETED_DONE, true],
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_CANCELED_ABORTED, false],
        ];
    }

    /** @test */
    public function it_ignores_orders_without_user()
    {
        $supplier = Supplier::factory()->createQuietly();

        Order::factory()->usingSupplier($supplier)->completed()->create(['user_id' => null]);
        Order::factory()->usingSupplier($supplier)->approved()->create(['user_id' => null]);

        $action = Mockery::mock(AddPoints::class);
        $action->shouldNotReceive('execute')->withAnyArgs();
        App::bind(AddPoints::class, fn() => $action);

        $command = new AddPointsUnprocessedOrdersCommand();
        $command->handle();
    }

    /** @test
     * @dataProvider  statusDataProvider
     */
    public function it_ignores_approved_or_completed_orders_that_already_earned_completed_points(int $substatusId)
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        Point::factory()->usingOrder($order)->create(['action' => Point::ACTION_ORDER_APPROVED]);

        $action = Mockery::mock(AddPoints::class);
        $action->shouldNotReceive('execute')->withAnyArgs();
        App::bind(AddPoints::class, fn() => $action);

        $command = new AddPointsUnprocessedOrdersCommand();
        $command->handle();
    }

    /** @test
     * @dataProvider statusDataProvider
     */
    public function it_adds_points_to_approved_or_completed_orders_that_already_earned_other_points(int $substatusId)
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        Point::factory()->usingOrder($order)->create(['action' => 'another-point']);

        $action = Mockery::mock(AddPoints::class);
        $action->shouldReceive('execute')->withAnyArgs()->once();
        App::bind(AddPoints::class, fn() => $action);

        $command = new AddPointsUnprocessedOrdersCommand();
        $command->handle();
    }

    public function statusDataProvider(): array
    {
        return [
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [Substatus::STATUS_APPROVED_DELIVERED],
            [Substatus::STATUS_COMPLETED_DONE],
        ];
    }
}
