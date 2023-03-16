<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Actions\Models\Supplier\PublishMessage as SupplierPublishMessage;
use App\Actions\Models\User\PublishMessage as UserPublishMessage;
use App\Constants\PubnubMessageTypes;
use App\Events\Order\Created as CreatedEvent;
use App\Events\Order\OrderEvent;
use App\Listeners\Order\SendChatApprovedNotification;
use App\Models\Order;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use PubNub\PubNub;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendChatApprovedNotificationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendChatApprovedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_calls_publish_message_for_user_and_supplier()
    {
        $userPublishMessage = Mockery::mock(UserPublishMessage::class)->makePartial();
        $userPublishMessage->shouldReceive('execute')->withNoArgs()->once();
        App::bind(UserPublishMessage::class, fn() => $userPublishMessage);

        $supplierPublishMessage = Mockery::mock(SupplierPublishMessage::class)->makePartial();
        $supplierPublishMessage->shouldReceive('execute')->withNoArgs()->once();
        App::bind(SupplierPublishMessage::class, fn() => $supplierPublishMessage);

        $pubnubChannel = Mockery::mock(PubnubChannel::class);

        $pubnubChannels = Mockery::mock(HasMany::class);
        $pubnubChannels->shouldReceive('scoped')->withAnyArgs()->once()->andReturn($pubnubChannels);
        $pubnubChannels->shouldReceive('first')->withNoArgs()->once()->andReturn($pubnubChannel);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('pubnubChannels')->withNoArgs()->once()->andReturn($pubnubChannels);

        $supplier = Mockery::mock(Supplier::class);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('supplier')->twice()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn('name Order');
        $order->shouldReceive('getAttribute')->with('bid_number')->once()->andReturn('Bid Number');
        $order->shouldReceive('getAttribute')->with('user')->twice()->andReturn($user);

        $event = Mockery::mock(OrderEvent::class);
        $event->shouldReceive('order')->withNoArgs()->once()->andReturn($order);

        $listener = App::make(SendChatApprovedNotification::class);

        $listener->handle($event);
    }

    /** @test */
    public function it_publishes_automatically_message_to_user_and_order_approved_message_to_supplier_in_the_pubnub_channel(
    )
    {
        $this->refreshDatabaseForSingleTest();

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

        $event    = new CreatedEvent($order);
        $listener = App::make(SendChatApprovedNotification::class);
        $listener->handle($event);
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel_with_bid_and_po_number_when_the_order_had_it()
    {
        $this->refreshDatabaseForSingleTest();

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $pubnubChannel = PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create([
            'bid_number'    => $bidNumber = 'bid_number',
            'name'          => $poNumber = 'po_number',
            'working_on_it' => 'John Doe',
        ]);

        $message               = PubnubMessageTypes::ORDER_APPROVED;
        $message['bid_number'] = $bidNumber;
        $message['po_number']  = $poNumber;

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

        $event    = new CreatedEvent($order);
        $listener = App::make(SendChatApprovedNotification::class);
        $listener->handle($event);
    }
}
