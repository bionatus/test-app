<?php

namespace Tests\Feature\Api\V4\Order\Name;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Order\NameController;
use App\Http\Requests\Api\V4\Order\Name\InvokeRequest;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see NameController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V4_ORDER_NAME_UPDATE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:updateName,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_returns_correct_resource()
    {

        $user  = User::factory()->create();
        $order = Order::factory()->usingUser($user)->create();
        OrderDelivery::factory()->shipmentDelivery()->usingOrder($order)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED))
            ->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]),
            [RequestKeys::NAME => $name = 'Test 1']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($order->getRouteKey(), $data['id']);
        $this->assertSame($name, $data['name']);
    }

    /** @test */
    public function it_updates_the_name_from_an_order()
    {

        $user  = User::factory()->create();
        $order = Order::factory()->usingUser($user)->create();
        OrderDelivery::factory()->shipmentDelivery()->usingOrder($order)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED))
            ->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]),
            [RequestKeys::NAME => $name = 'Test 1']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => $name,
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
        ]);
    }
}
