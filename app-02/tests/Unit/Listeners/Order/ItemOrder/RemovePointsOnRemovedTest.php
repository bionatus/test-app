<?php

namespace Tests\Unit\Listeners\Order\ItemOrder;

use App\Events\Order\ItemOrder\Removed;
use App\Listeners\Order\ItemOrder\RemovePointsOnRemoved;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Point;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class RemovePointsOnRemovedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(RemovePointsOnRemoved::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_removes_points_related_to_an_order()
    {
        $order        = Mockery::mock(Order::class);
        $user         = Mockery::mock(User::class);
        $itemOrder    = Mockery::mock(ItemOrder::class);
        $point        = Mockery::mock(Point::class);
        $hasMany      = Mockery::mock(HasMany::class);
        $morphMany    = Mockery::mock(MorphMany::class);
        $scopeBuilder = Mockery::mock(Builder::class);
        $itemOrder->shouldReceive('getAttribute')->with('order')->twice()->andReturn($order);
        $itemOrder->shouldReceive('getAttribute')->with('price')->once()->andReturn($price = 100);
        $itemOrder->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity = 2);
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $order->shouldReceive('getKey')->withNoArgs()->once()->andReturn($orderId = 10);
        $order->shouldReceive('points')->withNoArgs()->once()->andReturn($morphMany);
        $point->shouldReceive('getAttribute')->with('coefficient')->times(2)->andReturn($coefficient = 0.5);
        $point->shouldReceive('getAttribute')->with('multiplier')->times(2)->andReturn($multiplier = 5);
        $morphMany->shouldReceive('scoped')->once()->andReturn($scopeBuilder);
        $scopeBuilder->shouldReceive('first')->withNoArgs()->once()->andReturn($point);
        $user->shouldReceive('points')->withNoArgs()->once()->andReturn($hasMany);
        $user->shouldReceive('processLevel')->withNoArgs()->once();

        $data = [
            'object_id'     => $orderId,
            'action'        => Point::ACTION_ORDER_ITEM_REMOVED,
            'object_type'   => Order::MORPH_ALIAS,
            'coefficient'   => $coefficient,
            'multiplier'    => $multiplier,
            'points_earned' => floor($price * $quantity * $coefficient * $multiplier) * -1,
        ];
        $hasMany->shouldReceive('create')->with($data)->once();
        $event    = new Removed($itemOrder);
        $listener = App::make(RemovePointsOnRemoved::class);
        $listener->handle($event);
    }

    /** @test */
    public function it_does_not_remove_points_related_to_an_order_that_does_not_have_user()
    {
        $order        = Mockery::mock(Order::class);
        $itemOrder    = Mockery::mock(ItemOrder::class);
        $point        = Mockery::mock(Point::class);
        $morphMany    = Mockery::mock(MorphMany::class);
        $scopeBuilder = Mockery::mock(Builder::class);
        $itemOrder->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturnNull();
        $order->shouldReceive('points')->withNoArgs()->once()->andReturn($morphMany);
        $morphMany->shouldReceive('scoped')->once()->andReturn($scopeBuilder);
        $scopeBuilder->shouldReceive('first')->withNoArgs()->once()->andReturn($point);

        $event    = new Removed($itemOrder);
        $listener = App::make(RemovePointsOnRemoved::class);
        $listener->handle($event);
    }
}
