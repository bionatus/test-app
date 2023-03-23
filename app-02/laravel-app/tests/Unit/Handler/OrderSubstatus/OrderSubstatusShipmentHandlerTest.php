<?php

namespace Tests\Unit\Handler\OrderSubstatus;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Handlers\OrderSubstatus\OrderSubstatusShipmentHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class OrderSubstatusShipmentHandlerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_is_order_substatus_changed_interface()
    {
        $reflection = new ReflectionClass(OrderSubstatusShipmentHandler::class);

        $this->assertTrue($reflection->implementsInterface(OrderSubstatusUpdated::class));
    }

    /** @test */
    public function it_updates_an_order_substatus_to_pending_approval_quote_updated()
    {
        $order = Mockery::mock(Order::class);

        $changeStatus = Mockery::mock(ChangeStatus::class);
        $changeStatus->shouldReceive('execute')->withNoArgs()->once()->andReturn($order);
        App::bind(ChangeStatus::class, fn() => $changeStatus);

        $handler = App::make(OrderSubstatusShipmentHandler::class);
        $handler->processPendingApprovalQuoteNeeded($order);
    }
}
