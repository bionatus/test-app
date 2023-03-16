<?php

namespace Tests\Unit\Jobs\User;

use App;
use App\Constants\PubnubMessageTypes;
use App\Jobs\User\PublishOrderCanceledMessage;
use App\Models\PubnubChannel;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PubNub\PubNub;
use ReflectionClass;
use Tests\TestCase;

class PublishOrderCanceledMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(PublishOrderCanceledMessage::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new PublishOrderCanceledMessage(new PubnubChannel(), null);

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_publishes_a_order_canceled_message_in_the_pubnub_channel()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);

        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $pubnubChannel->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('route key');

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::ORDER_CANCELED)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        (new PublishOrderCanceledMessage($pubnubChannel, null))->handle();
    }

    /** @test */
    public function it_publishes_a_order_canceled_message_in_the_pubnub_channel_with_bid_number()
    {
        $message               = PubnubMessageTypes::ORDER_CANCELED;
        $message['bid_number'] = $bidNumber = 'bid number';

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);

        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $pubnubChannel->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('route key');

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($message)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        (new PublishOrderCanceledMessage($pubnubChannel, $bidNumber))->handle();
    }
}
