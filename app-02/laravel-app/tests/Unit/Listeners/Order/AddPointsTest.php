<?php

namespace Tests\Unit\Listeners\Order;

use App\Actions\Models\Order\AddPoints as AddPointsAction;
use App\Events\Order\LegacyCompleted;
use App\Listeners\Order\AddPoints;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class AddPointsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(AddPoints::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_calls_add_point_action_if_user_exist_and_the_order_does_not_has_points()
    {
        $morphMany    = Mockery::mock(MorphMany::class);
        $scopeBuilder = Mockery::mock(Builder::class);
        $user         = Mockery::mock(User::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $order->shouldReceive('points')->withNoArgs()->once()->andReturn($morphMany);
        $order->shouldReceive('isCompleted')->withNoArgs()->once()->andReturnTrue();

        $morphMany->shouldReceive('scoped')->once()->andReturn($scopeBuilder);
        $scopeBuilder->shouldReceive('exists')->withNoArgs()->once()->andReturnFalse();

        $action = Mockery::mock(AddPointsAction::class);
        $action->shouldReceive('execute')->withNoArgs()->once();
        App::bind(AddPointsAction::class, fn() => $action);

        $event    = new LegacyCompleted($order);
        $listener = App::make(AddPoints::class);
        $listener->handle($event);
    }

    /** @test */
    public function it_should_not_call_add_point_action_if_user_not_exist()
    {
        $morphMany    = Mockery::mock(MorphMany::class);
        $scopeBuilder = Mockery::mock(Builder::class);
        $order        = Mockery::mock(Order::class);

        $order->shouldReceive('getAttribute')->with('user')->once()->andReturnNull();
        $order->shouldReceive('points')->withNoArgs()->once()->andReturn($morphMany);
        $order->shouldReceive('isCompleted')->withNoArgs()->once()->andReturnTrue();

        $morphMany->shouldReceive('scoped')->once()->andReturn($scopeBuilder);
        $scopeBuilder->shouldReceive('exists')->withNoArgs()->once()->andReturnFalse();

        $action = Mockery::mock(AddPointsAction::class);
        $action->shouldNotReceive('execute');
        App::bind(AddPointsAction::class, fn() => $action);

        $event    = new LegacyCompleted($order);
        $listener = App::make(AddPoints::class);
        $listener->handle($event);
    }

    /** @test */
    public function it_should_not_call_add_point_action_if_points_already_added()
    {
        $morphMany    = Mockery::mock(MorphMany::class);
        $user         = Mockery::mock(User::class);
        $scopeBuilder = Mockery::mock(Builder::class);
        $order        = Mockery::mock(Order::class);

        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $order->shouldReceive('points')->withNoArgs()->once()->andReturn($morphMany);
        $order->shouldReceive('isCompleted')->withNoArgs()->once()->andReturnFalse();

        $morphMany->shouldReceive('scoped')->once()->andReturn($scopeBuilder);
        $scopeBuilder->shouldReceive('exists')->withNoArgs()->once()->andReturnTrue();

        $action = Mockery::mock(AddPointsAction::class);
        $action->shouldNotReceive('execute');
        App::bind(AddPointsAction::class, fn() => $action);

        $event    = new LegacyCompleted($order);
        $listener = App::make(AddPoints::class);
        $listener->handle($event);
    }
}
