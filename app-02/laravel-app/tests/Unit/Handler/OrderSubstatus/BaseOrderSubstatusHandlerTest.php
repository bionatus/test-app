<?php

namespace Tests\Unit\Handler\OrderSubstatus;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Handlers\OrderSubstatus\BaseOrderSubstatusHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseOrderSubstatusHandlerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_is_order_substatus_changed_interface()
    {
        $reflection = new ReflectionClass(BaseOrderSubstatusHandler::class);

        $this->assertTrue($reflection->implementsInterface(OrderSubstatusUpdated::class));
    }

    /** @test */
    public function it_changes_substatus()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getKey')->withNoArgs()->andReturn(1);

        $action = Mockery::mock(ChangeStatus::class);
        $action->shouldReceive('execute')->withNoArgs()->once()->andReturn($order);
        App::bind(ChangeStatus::class, fn() => $action);

        $stub = $this->getMockForAbstractClass(BaseOrderSubstatusHandler::class);
        $stub->changeSubstatus($order, 100);
    }
}
