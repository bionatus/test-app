<?php

namespace Tests\Feature\LiveApi\V1\Unauthenticated\Order\Approve;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\LegacyApproved;
use App\Events\Order\ApprovedByTeam;
use App\Http\Controllers\LiveApi\V1\Unauthenticated\Order\ApproveController;
use App\Http\Requests\LiveApi\V1\Unauthenticated\Order\Approve\InvokeRequest;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see ApproveController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_UNAUTHENTICATED_ORDER_APPROVE_STORE;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName,
            ['can:approveUnauthenticated,' . RouteParameters::UNAUTHENTICATED_ORDER]);
    }

    /** @test */
    public function it_executes_approve_action()
    {
        Event::fake([LegacyApproved::class, ApprovedByTeam::class]);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingUser($user)->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $action = Mockery::mock(App\Actions\Models\Order\Approve::class);
        $action->shouldReceive('execute')->once()->andReturn($order);
        App::bind(App\Actions\Models\Order\Approve::class, fn() => $action);

        $this->login($user);
        $response = $this->post(URL::route($this->routeName,
            [RouteParameters::UNAUTHENTICATED_ORDER => $order->getRouteKey()]));
        $response->assertStatus(Response::HTTP_CREATED);
    }

    /** @test */
    public function it_approves_an_order_setting_a_name()
    {
        Event::fake([LegacyApproved::class, ApprovedByTeam::class]);

        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->pendingApproval()->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        $expectedName = 'Fake order name';
        OrderDelivery::factory()->usingOrder($order)->create();

        $response = $this->post(URL::route($this->routeName,
            [RouteParameters::UNAUTHENTICATED_ORDER => $order->getRouteKey()]), [RequestKeys::NAME => $expectedName]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($order->getRouteKey(), $data['id']);

        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => $expectedName,
        ]);
    }

    /** @test */
    public function it_approves_an_order_without_setting_a_name()
    {
        Event::fake([LegacyApproved::class, ApprovedByTeam::class]);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create();

        $response = $this->post(URL::route($this->routeName,
            [RouteParameters::UNAUTHENTICATED_ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($order->getRouteKey(), $data['id']);

        $this->assertSame($order->lastStatus->substatus_id, Substatus::STATUS_APPROVED_AWAITING_DELIVERY);
        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => null
        ]);
    }

    /** @test */
    public function it_dispatches_an_approved_by_team_event()
    {
        Event::fake([LegacyApproved::class, ApprovedByTeam::class]);

        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create();

        $response = $this->post(URL::route($this->routeName,
            [RouteParameters::UNAUTHENTICATED_ORDER => $order->getRouteKey()]));
        $response->assertStatus(Response::HTTP_CREATED);
        Event::assertDispatched(ApprovedByTeam::class);
    }
}
