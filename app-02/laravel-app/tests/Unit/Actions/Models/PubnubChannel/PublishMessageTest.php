<?php

namespace Tests\Unit\Actions\Models\PubnubChannel;

use App;
use App\Actions\Models\PubnubChannel\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use PubNub\PubNub;
use Tests\TestCase;

class PublishMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_publishes_a_message()
    {
        $lastMessageAtField = 'supplier_last_message_at';
        $message            = PubnubMessageTypes::NEW_ORDER;
        $supplier           = Supplier::factory()->createQuietly();
        $pubnubChannel      = PubnubChannel::factory()->usingSupplier($supplier)->create();
        $senderUuid         = $supplier->getRouteKey();

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::NEW_ORDER)->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        $this->publishMessageStub($pubnubChannel, $message, $senderUuid, $lastMessageAtField)->execute();
    }

    /** @test */
    public function it_updates_a_pubnub_channel_last_message_at_field()
    {
        $lastMessageAtField = 'supplier_last_message_at';
        $message            = PubnubMessageTypes::NEW_ORDER;
        $supplier           = Supplier::factory()->createQuietly();
        $pubnubChannel      = PubnubChannel::factory()->usingSupplier($supplier)->create([
            $lastMessageAtField => $oldLastMessageAt = Carbon::now()->subDay(),
        ]);
        $senderUuid         = $supplier->getRouteKey();

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::NEW_ORDER)->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        $this->publishMessageStub($pubnubChannel, $message, $senderUuid, $lastMessageAtField)->execute();

        $this->assertTrue($oldLastMessageAt->isBefore($pubnubChannel->fresh()->$lastMessageAtField));
    }

    private function publishMessageStub($pubnubChannel, $message, $senderUuid, $lastMessageAtField): PublishMessage
    {
        return new class($pubnubChannel, $message, $senderUuid, $lastMessageAtField) extends PublishMessage {
        };
    }
}
