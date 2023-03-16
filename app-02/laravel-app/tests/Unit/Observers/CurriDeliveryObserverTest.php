<?php

namespace Tests\Unit\Observers;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Events\Order\Delivery\Curri\ArrivedAtDestination;
use App\Events\Order\Delivery\Curri\OnRoute;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Observers\CurriDeliveryObserver;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CurriDeliveryObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_not_dispatch_an_event_on_updated_if_status_is_not_dirty()
    {
        Event::fake(OnRoute::class);

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('isDirty')->with('status')->once()->andReturnFalse();

        (new CurriDeliveryObserver())->updated($curriDelivery);

        Event::assertNotDispatched(OnRoute::class);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_dispatches_the_on_route_event_on_updated(
        bool $isDispatched,
        string $originalStatus,
        string $status
    ) {
        Event::fake([OnRoute::class, ArrivedAtDestination::class]);

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('isDirty')->with('status')->once()->andReturnTrue();
        $curriDelivery->shouldReceive('getOriginal')->with('status')->once()->andReturn($originalStatus);

        $finishedStatuses = CurriDelivery::DELIVERY_FINISHED_STATUSES;
        if (!in_array($originalStatus, $finishedStatuses) && in_array($status, $finishedStatuses)) {
            $order         = Mockery::mock(Order::class);
            $orderDelivery = Mockery::mock(OrderDelivery::class);
            $orderDelivery->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);
            $curriDelivery->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

            $changeStatus = Mockery::mock(ChangeStatus::class);
            $changeStatus->shouldReceive('execute')->withNoArgs()->once();
            App::bind(ChangeStatus::class, fn() => $changeStatus);
        }

        $curriDelivery->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);

        (new CurriDeliveryObserver())->updated($curriDelivery);

        if ($isDispatched) {
            Event::assertDispatched(OnRoute::class);
        } else {
            Event::assertNotDispatched(OnRoute::class);
        }
    }

    public function dataProvider(): array
    {
        $otherStatus = 'other status';

        return [
            //$isDispatched, $originalStatus, $status
            [true, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN],
            [true, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN],
            [true, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION],
            [true, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION],
            [false, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [false, CurriDelivery::DELIVERY_STATUS_PENDING, $otherStatus],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, CurriDelivery::DELIVERY_STATUS_PENDING],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN],
            [
                false,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION,
            ],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, $otherStatus],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_PENDING],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, $otherStatus],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION, CurriDelivery::DELIVERY_STATUS_PENDING],
            [
                false,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN,
            ],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN],
            [
                false,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION,
                CurriDelivery::DELIVERY_STATUS_AT_DESTINATION,
            ],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION, $otherStatus],
            [false, $otherStatus, CurriDelivery::DELIVERY_STATUS_PENDING],
            [true, $otherStatus, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN],
            [true, $otherStatus, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN],
            [true, $otherStatus, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION],
            [true, $otherStatus, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION],
            [false, $otherStatus, CurriDelivery::DELIVERY_STATUS_DELIVERED],
        ];
    }

    /** @test
     * @dataProvider arrivedAtDestinationDataProvider
     */
    public function it_dispatches_the_arrived_at_destination_event_and_calls_change_status_action_on_updated(
        bool $isDispatched,
        string $originalStatus,
        string $status
    ) {
        Event::fake([OnRoute::class, ArrivedAtDestination::class]);

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('isDirty')->with('status')->once()->andReturnTrue();
        $curriDelivery->shouldReceive('getOriginal')->with('status')->once()->andReturn($originalStatus);

        $finishedStatuses = CurriDelivery::DELIVERY_FINISHED_STATUSES;
        if (!in_array($originalStatus, $finishedStatuses) && in_array($status, $finishedStatuses)) {
            $order         = Mockery::mock(Order::class);
            $orderDelivery = Mockery::mock(OrderDelivery::class);
            $orderDelivery->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);
            $curriDelivery->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

            $changeStatus = Mockery::mock(ChangeStatus::class);
            $changeStatus->shouldReceive('execute')->withNoArgs()->once();
            App::bind(ChangeStatus::class, fn() => $changeStatus);
        }

        $curriDelivery->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);

        (new CurriDeliveryObserver())->updated($curriDelivery);

        if ($isDispatched) {
            Event::assertDispatched(ArrivedAtDestination::class);
        } else {
            Event::assertNotDispatched(ArrivedAtDestination::class);
        }
    }

    public function arrivedAtDestinationDataProvider(): array
    {
        $otherStatus = 'other status';

        return [
            //$isDispatched, $originalStatus, $status
            [true, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION],
            [true, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [true, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION],
            [true, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [true, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION],
            [true, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [
                true,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION,
                CurriDelivery::DELIVERY_STATUS_AT_DESTINATION,
            ],
            [true, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [true, $otherStatus, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION],
            [true, $otherStatus, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [false, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN],
            [false, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN],
            [false, CurriDelivery::DELIVERY_STATUS_PENDING, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION],
            [false, CurriDelivery::DELIVERY_STATUS_PENDING, $otherStatus],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, CurriDelivery::DELIVERY_STATUS_PENDING],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN],
            [
                false,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION,
            ],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN, $otherStatus],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_PENDING],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION],
            [false, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN, $otherStatus],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION, CurriDelivery::DELIVERY_STATUS_PENDING],
            [
                false,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION,
                CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN,
            ],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN],
            [false, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION, $otherStatus],
            [false, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION, $otherStatus],
            [false, CurriDelivery::DELIVERY_STATUS_AT_DESTINATION, CurriDelivery::DELIVERY_STATUS_DELIVERED],
            [false, CurriDelivery::DELIVERY_STATUS_DELIVERED, $otherStatus],
            [false, $otherStatus, CurriDelivery::DELIVERY_STATUS_PENDING],
            [false, $otherStatus, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_ORIGIN],
            [false, $otherStatus, CurriDelivery::DELIVERY_STATUS_AT_ORIGIN],
            [false, $otherStatus, CurriDelivery::DELIVERY_STATUS_EN_ROUTE_TO_DESTINATION],
        ];
    }
}
