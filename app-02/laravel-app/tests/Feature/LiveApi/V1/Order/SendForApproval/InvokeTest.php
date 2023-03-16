<?php

namespace Tests\Feature\LiveApi\V1\Order\SendForApproval;

use App;
use App\Constants\PubnubMessageTypes;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\SentForApproval;
use App\Http\Controllers\LiveApi\V1\Order\SendForApprovalController;
use App\Http\Resources\LiveApi\V1\Order\Unprocessed\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\PubnubChannel;
use App\Models\Staff;
use App\Models\Substatus;
use Auth;
use Config;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use PubNub\PubNub;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SendForApprovalController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_SEND_FOR_APPROVAL_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->createQuietly()->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:sendForApproval,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_sends_an_order_for_approval()
    {
        Event::fake(SentForApproval::class);

        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;

        $order = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create([
                'date'       => Carbon::now(),
                'start_time' => Carbon::createFromTime(9)->format('H:i'),
                'end_time'   => Carbon::createFromTime(12)->format('H:i'),
            ]);
        });

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertSame($order->lastStatus->substatus_id, Substatus::STATUS_PENDING_APPROVAL_FULFILLED);
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_PENDING_APPROVAL_FULFILLED,
        ]);
    }

    /** @test */
    public function it_dispatches_a_sent_for_approval_event()
    {
        Event::fake(SentForApproval::class);

        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create([
                'date'       => Carbon::now(),
                'start_time' => Carbon::createFromTime(9)->format('H:i'),
                'end_time'   => Carbon::createFromTime(12)->format('H:i'),
            ]);
        });

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]));
        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(SentForApproval::class);
    }

    /** @test */
    public function it_publishes_a_message_of_type_sent_for_approval_in_the_pubnub_channel()
    {
        Event::fake(SentForApproval::class);

        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create([
                'date'       => Carbon::now(),
                'start_time' => Carbon::createFromTime(9)->format('H:i'),
                'end_time'   => Carbon::createFromTime(12)->format('H:i'),
            ]);
        });

        $pubnubChannel = PubnubChannel::factory()->usingSupplier($order->supplier)->usingUser($order->user)->create();

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->twice()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::ORDER_SENT_FOR_APPROVAL)->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->twice()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->twice()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]));
        $response->assertStatus(Response::HTTP_CREATED);
    }

    /** @test */
    public function it_publishes_a_message_of_type_sent_for_approval_link_in_the_pubnub_channel()
    {
        Event::fake(SentForApproval::class);

        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create([
                'date'       => Carbon::now(),
                'start_time' => Carbon::createFromTime(9)->format('H:i'),
                'end_time'   => Carbon::createFromTime(12)->format('H:i'),
            ]);
        });
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($order->supplier)->usingUser($order->user)->create();
        $message       = PubnubMessageTypes::ORDER_SENT_FOR_APPROVAL_LINK;
        $orderUuid     = $order->getRouteKey();
        Config::set('live.url', $liveUrl = 'http://test.com/');
        Config::set('live.order.summary', $orderSummaryUrl = '#/order-summary?order={order}');
        $shareLink             = $liveUrl . Str::replace('{order}', $orderUuid, $orderSummaryUrl);
        $message['orderId']    = $orderUuid;
        $message['order_id']   = $orderUuid;
        $message['shareLink']  = $shareLink;
        $message['share_link'] = $shareLink;

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->twice()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($message)->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->twice()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->twice()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]));
        $response->assertStatus(Response::HTTP_CREATED);
    }
}
