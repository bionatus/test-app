<?php

namespace Tests\Unit\Actions\Models\User;

use App;
use App\Actions\Models\User\PublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
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
        $message       = PubnubMessageTypes::NEW_ORDER;
        $supplier      = Supplier::factory()->createQuietly();
        $user          = User::factory()->create();
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::NEW_ORDER)->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        (new PublishMessage($pubnubChannel, $message, $user))->execute();
    }

    /** @test */
    public function it_updates_a_pubnub_channel_last_message_at_field()
    {
        $lastMessageAtField = 'user_last_message_at';
        $message            = PubnubMessageTypes::NEW_ORDER;
        $supplier           = Supplier::factory()->createQuietly();
        $user               = User::factory()->create();
        $pubnubChannel      = PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create([
            $lastMessageAtField => $oldLastMessageAt = Carbon::now()->subDay(),
        ]);

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::NEW_ORDER)->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        (new PublishMessage($pubnubChannel, $message, $user))->execute();

        $this->assertTrue($oldLastMessageAt->isBefore($pubnubChannel->fresh()->$lastMessageAtField));
    }
}
