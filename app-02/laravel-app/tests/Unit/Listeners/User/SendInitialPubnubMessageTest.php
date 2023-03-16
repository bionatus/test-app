<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Actions\Models\Supplier\PublishMessage;
use App\Events\PubnubChannel\Created;
use App\Listeners\User\SendInitialPubnubMessage;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use PubNub\PubNub;
use ReflectionClass;
use Tests\TestCase;

class SendInitialPubnubMessageTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendInitialPubnubMessage::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_called_publish_message_action()
    {
        $publishMessage = Mockery::mock(PublishMessage::class)->makePartial();
        $publishMessage->shouldReceive('execute')->withAnyArgs()->once();
        App::bind(PublishMessage::class, fn() => $publishMessage);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturn('Street 111');
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn('New York');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn('Supplier INC');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn('John');

        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getAttribute')->withArgs(['supplier'])->twice()->andReturn($supplier);
        $pubnubChannel->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);

        $event = Mockery::mock(Created::class);
        $event->shouldReceive('pubnubChannel')->withNoArgs()->once()->andReturn($pubnubChannel);

        $listener = App::make(SendInitialPubnubMessage::class);

        $listener->handle($event);
    }

    /** @test */
    public function it_sets_correct_message_payload()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['address'])
            ->once()
            ->andReturn($supplierAddress = 'Street 111');
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($supplierCity = 'New York');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($supplierName = 'Supplier INC');
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('uuid');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($userName = 'John');

        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getAttribute')->withArgs(['supplier'])->twice()->andReturn($supplier);
        $pubnubChannel->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);
        $pubnubChannel->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('channel');

        $message = [
            'text' => "Hey **{$userName}**! You're now connected with **{$supplierName}** at **{$supplierAddress}** in **{$supplierCity}**. You can now send quote requests to this location and message here directly!",
            'type' => 'auto_message',
        ];

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($message)->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnSelf();
        App::bind(PubNub::class, fn() => $pubnub);

        $event = Mockery::mock(Created::class);
        $event->shouldReceive('pubnubChannel')->withNoArgs()->once()->andReturn($pubnubChannel);

        $listener = App::make(SendInitialPubnubMessage::class);

        $listener->handle($event);
    }
}
