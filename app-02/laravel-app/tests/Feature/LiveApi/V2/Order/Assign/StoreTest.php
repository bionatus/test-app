<?php

namespace Tests\Feature\LiveApi\V2\Order\Assign;

use App;
use App\Actions\Models\SettingUser\GetNotificationSetting;
use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Events\Order\Assigned;
use App\Http\Controllers\LiveApi\V2\Order\AssignController;
use App\Http\Requests\LiveApi\V2\Order\Assignment\StoreRequest;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use App\Models\OrderStaff;
use App\Models\OrderSubstatus;
use App\Models\PubnubChannel;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use Auth;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use PubNub\PubNub;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see AssignController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_ASSIGNMENT_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $supplier = Supplier::factory()->createQuietly();
        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->usingSupplier($supplier)->create()->getRouteKey()]));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_uses_assign_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:assign,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_create_order_staff_successfully()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->counter()->usingSupplier($supplier)->create(['name' => 'Fake name']);
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $action = Mockery::mock(GetNotificationSetting::class);
        $action->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSetting::class, fn() => $action);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [RequestKeys::STAFF => $staff->getRouteKey()]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);
        $this->assertDatabaseHas(OrderStaff::tableName(),
            ['order_id' => $order->getKey(), 'staff_id' => $staff->getKey()]);
    }

    /** @test */
    public function it_change_order_substatus()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->counter()->usingSupplier($supplier)->create(['name' => 'Fake name']);
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $action = Mockery::mock(GetNotificationSetting::class);
        $action->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSetting::class, fn() => $action);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [RequestKeys::STAFF => $staff->getRouteKey()]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);
        $this->assertDatabaseHas(OrderSubstatus::tableName(),
            ['order_id' => $order->getKey(), 'substatus_id' => Substatus::STATUS_PENDING_ASSIGNED]);
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->counter()->usingSupplier($supplier)->create(['name' => 'Fake name']);
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->usingUser($order->user)->create();

        $message = [
            'text' => $staff->name . ' is working on your quote. Stay tuned!',
            'type' => 'auto_message',
        ];

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->with($pubnubChannel->channel)->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($message)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        $action = Mockery::mock(GetNotificationSetting::class);
        $action->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSetting::class, fn() => $action);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [RequestKeys::STAFF => $staff->getRouteKey()]);
    }

    /** @test */
    public function it_dispatches_a_assigned_event()
    {
        Event::fake([Assigned::class]);

        $staff    = Staff::factory()->counter()->createQuietly(['name' => 'Fake name']);
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $action = Mockery::mock(GetNotificationSetting::class);
        $action->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSetting::class, fn() => $action);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [RequestKeys::STAFF => $staff->getRouteKey()]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(Assigned::class);
    }
}
