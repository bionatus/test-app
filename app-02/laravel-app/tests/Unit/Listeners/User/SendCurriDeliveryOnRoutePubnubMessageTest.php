<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Actions\Models\Supplier\PublishMessage;
use App\Events\Order\Delivery\Curri\OnRoute;
use App\Listeners\User\SendCurriDeliveryOnRoutePubnubMessage;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use PubNub\PubNub;
use ReflectionClass;
use Tests\TestCase;

class SendCurriDeliveryOnRoutePubnubMessageTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendCurriDeliveryOnRoutePubnubMessage::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_calls_publish_message_action()
    {
        $publishMessage = Mockery::mock(PublishMessage::class)->makePartial();
        $publishMessage->shouldReceive('execute')->withNoArgs()->once();
        App::bind(PublishMessage::class, fn() => $publishMessage);

        $getPubnubChannel = Mockery::mock(GetPubnubChannel::class);
        $getPubnubChannel->shouldReceive('execute')->withNoArgs()->once();
        App::bind(GetPubnubChannel::class, fn() => $getPubnubChannel);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturnNull();

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $curriDelivery->shouldReceive('getAttribute')->with('tracking_url')->once()->andReturnNull();

        $event = Mockery::mock(OnRoute::class);
        $event->shouldReceive('curriDelivery')->withNoArgs()->once()->andReturn($curriDelivery);

        $listener = App::make(SendCurriDeliveryOnRoutePubnubMessage::class);

        $listener->handle($event);
    }

    /** @test */
    public function it_sets_correct_message_payload()
    {
        $message = [
            'text'         => 'Your driver is on route! Click below to track their progress.',
            'tracking_url' => $trackingUrl = 'tracking_url',
            'type'         => 'curri',
        ];

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($message)->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnSelf();
        App::bind(PubNub::class, fn() => $pubnub);

        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('channel');

        $getPubnubChannel = Mockery::mock(GetPubnubChannel::class);
        $getPubnubChannel->shouldReceive('execute')->withNoArgs()->once()->andReturn($pubnubChannel);
        App::bind(GetPubnubChannel::class, fn() => $getPubnubChannel);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('supplier');

        $user = Mockery::mock(User::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $curriDelivery->shouldReceive('getAttribute')->with('tracking_url')->once()->andReturn($trackingUrl);

        $event = Mockery::mock(OnRoute::class);
        $event->shouldReceive('curriDelivery')->withNoArgs()->once()->andReturn($curriDelivery);

        $listener = App::make(SendCurriDeliveryOnRoutePubnubMessage::class);

        $listener->handle($event);
    }
}
