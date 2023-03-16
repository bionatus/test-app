<?php

namespace Tests\Unit\Actions\Models\Supplier;

use App;
use App\Actions\Models\Supplier\PublishMessage;
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
        $message            = PubnubMessageTypes::NEW_ORDER;
        $supplier           = Supplier::factory()->createQuietly();
        $pubnubChannel      = PubnubChannel::factory()->usingSupplier($supplier)->create();

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::NEW_ORDER)->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        (new PublishMessage($pubnubChannel, $message, $supplier))->execute();
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

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::NEW_ORDER)->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        (new PublishMessage($pubnubChannel, $message, $supplier))->execute();

        $this->assertTrue($oldLastMessageAt->isBefore($pubnubChannel->fresh()->$lastMessageAtField));
    }
}
