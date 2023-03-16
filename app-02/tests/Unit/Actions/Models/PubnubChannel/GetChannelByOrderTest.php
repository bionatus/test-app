<?php

namespace Tests\Unit\Actions\Models\PubnubChannel;

use App\Actions\Models\PubnubChannel\GetChannelByOrder;
use App\Models\Order;
use App\Models\OrderLockedData;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GetChannelByOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_return_pubnub_channel_when_user_is_deleted()
    {
        $orderLockedData = Mockery::mock(OrderLockedData::class);
        $orderLockedData->shouldReceive('getAttribute')
            ->with('channel')
            ->andReturn($channel = 'order-channel-supplier-user');

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('orderLockedData')->once()->andReturn($orderLockedData);

        $action        = new GetChannelByOrder($order);
        $channelAction = $action->execute();

        $this->assertSame($channelAction, $channel);
    }

    /** @test */
    public function it_return_pubnub_channel_when_user_is_not_null()
    {
        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($channel = 'supplier-5b70d12b-31a3-46ff-824f-aec2ef6a18f8.user-77');

        $pubnubChannels = Mockery::mock(HasMany::class);
        $pubnubChannels->shouldReceive('scoped')->withAnyArgs()->once()->andReturn($pubnubChannels);
        $pubnubChannels->shouldReceive('first')->withNoArgs()->once()->andReturn($pubnubChannel);

        $supplier = Mockery::mock(Supplier::class);
        $user     = Mockery::mock(User::class);
        $user->shouldReceive('pubnubChannels')->withNoArgs()->once()->andReturn($pubnubChannels);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('user')->twice()->andReturn($user);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);

        $actionByOrder       = new GetChannelByOrder($order);
        $returnPubnubChannel = $actionByOrder->execute();

        $this->assertSame($channel, $returnPubnubChannel);
    }
}
