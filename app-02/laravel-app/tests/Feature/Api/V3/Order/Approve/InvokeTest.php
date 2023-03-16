<?php

namespace Tests\Feature\Api\V3\Order\Approve;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\LegacyApproved;
use App\Http\Controllers\Api\V3\Order\ApproveController;
use App\Http\Requests\Api\V3\Order\Approve\InvokeRequest;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ApproveController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ORDER_APPROVE_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:approve,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_executes_approve_action()
    {
        Event::fake(LegacyApproved::class);

        $action = Mockery::mock(App\Actions\Models\Order\Approve::class);
        $action->shouldReceive('execute')->once();
        App::bind(App\Actions\Models\Order\Approve::class, fn() => $action);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingUser($user)->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);

        $this->login($user);
        $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
    }

    /** @test */
    public function it_returns_correct_resource()
    {
        Event::fake(LegacyApproved::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingUser($user)->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($order->getRouteKey(), $data['id']);

        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => null,
        ]);

        $this->assertSame(Substatus::STATUS_APPROVED_AWAITING_DELIVERY, $order->lastStatus->substatus_id);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_PENDING_APPROVAL_FULFILLED,
            'detail'       => null,
        ]);
    }

    /** @test */
    public function it_returns_correct_resource_with_a_name()
    {
        Event::fake(LegacyApproved::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingUser($user)->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });
        $expectedName = 'Fake order name';

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]),
            [RequestKeys::NAME => $expectedName]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($order->getRouteKey(), $data['id']);

        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => $expectedName,
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
            'detail'       => null,
        ]);
    }
}
