<?php

namespace Tests\Unit\Actions\Models\Order;

use App;
use App\Actions\Models\Order\Approve;
use App\Constants\PubnubMessageTypes;
use App\Events\Order\LegacyApproved;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\PubnubChannel;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use PubNub\PubNub;
use Tests\TestCase;

class LegacyApproveTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    /** @test */
    public function it_approves_an_order_setting_a_name()
    {
        Event::fake(LegacyApproved::class);

        $order = Order::factory()->createQuietly([
            'working_on_it' => 'John Doe',
        ]);
        $name  = 'Fake order name';

        (new Approve($order, $name))->execute();

        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => $name,
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
        ]);
    }

    /** @test */
    public function it_approves_an_order_without_setting_a_name()
    {
        Event::fake(LegacyApproved::class);

        $order = Order::factory()->createQuietly([
            'working_on_it' => 'John Doe',
        ]);

        (new Approve($order, null))->execute();

        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => null,
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
        ]);
    }

    /** @test */
    public function it_dispatches_an_approved_event()
    {
        Event::fake(LegacyApproved::class);

        $order = Order::factory()->createQuietly([
            'working_on_it' => 'John Doe',
        ]);

        (new Approve($order, null))->execute();

        Event::assertDispatched(LegacyApproved::class);
    }

    /** @test */
    public function it_publishes_automatically_message_to_user_and_order_approved_message_to_supplier_in_the_pubnub_channel(
    )
    {
        Event::fake(LegacyApproved::class);

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $pubnubChannel = PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->with($pubnubChannel->channel)->twice()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::ORDER_APPROVED)->once()->andReturnSelf();
        $pubnub->shouldReceive('message')
            ->with(PubnubMessageTypes::ORDER_APPROVED_AUTOMATIC_MESSAGE)
            ->once()
            ->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->twice()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->twice()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        (new Approve($order, null))->execute();
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel_with_bid_number_when_the_order_had_it()
    {
        Event::fake(LegacyApproved::class);

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $pubnubChannel = PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create([
            'bid_number'    => $bidNumber = 'bid_number',
            'working_on_it' => 'John Doe',
        ]);

        $message               = PubnubMessageTypes::ORDER_APPROVED;
        $message['bid_number'] = $bidNumber;

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->with($pubnubChannel->channel)->twice()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($message)->once()->andReturnSelf();
        $pubnub->shouldReceive('message')
            ->with(PubnubMessageTypes::ORDER_APPROVED_AUTOMATIC_MESSAGE)
            ->once()
            ->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->twice()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->twice()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        (new Approve($order, null))->execute();
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel_with_po_number_when_the_order_name_is_set()
    {
        Event::fake(LegacyApproved::class);

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $pubnubChannel = PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        $name          = 'Fake order name';

        $message              = PubnubMessageTypes::ORDER_APPROVED;
        $message['po_number'] = $name;

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->with($pubnubChannel->channel)->twice()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($message)->once()->andReturnSelf();
        $pubnub->shouldReceive('message')
            ->with(PubnubMessageTypes::ORDER_APPROVED_AUTOMATIC_MESSAGE)
            ->once()
            ->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->twice()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->twice()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        (new Approve($order, $name))->execute();
    }
}
