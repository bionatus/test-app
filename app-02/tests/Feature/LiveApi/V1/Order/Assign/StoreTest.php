<?php

namespace Tests\Feature\LiveApi\V1\Order\Assign;

use App;
use App\Actions\Models\SettingUser\GetNotificationSetting;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\Assigned;
use App\Http\Controllers\LiveApi\V1\Order\AssignController;
use App\Http\Requests\LiveApi\V1\Order\Assignment\StoreRequest;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\PubnubChannel;
use App\Models\Staff;
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

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_ASSIGNMENT_STORE;

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
    public function it_sets_working_on_it_field_successfully()
    {
        $staff    = Staff::factory()->createQuietly(['name' => 'Example']);
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });
        $workingOnIt = 'John Doe';

        Auth::shouldUse('live');
        $this->login($staff);

        $action = Mockery::mock(GetNotificationSetting::class);
        $action->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSetting::class, fn() => $action);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [RequestKeys::NAME => $workingOnIt]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(Order::tableName(), ['id' => $order->getKey(), 'working_on_it' => $workingOnIt]);
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel()
    {
        $supplier    = Supplier::factory()->createQuietly();
        $order       = Order::factory()->usingSupplier($supplier)->pending()->create();
        $workingOnIt = 'John Doe';

        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->usingUser($order->user)->create();

        $message = [
            'text' => $workingOnIt . ' is working on your quote. Stay tuned!',
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

        $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]),
            [RequestKeys::NAME => $workingOnIt]);
    }

    /** @test */
    public function it_dispatches_an_assigned_event()
    {
        Event::fake([Assigned::class]);

        $staff    = Staff::factory()->counter()->createQuietly([$name = 'name' => 'Fake name']);
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $action = Mockery::mock(GetNotificationSetting::class);
        $action->shouldReceive('execute')->withNoArgs()->andReturn(true);
        App::bind(GetNotificationSetting::class, fn() => $action);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [RequestKeys::NAME => $name]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(Assigned::class);
    }
}
