<?php

namespace Tests\Feature\LiveApi\V1\Unauthenticated\Order;

use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Unauthenticated\OrderController;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see OrderController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_UNAUTHENTICATED_ORDER_SHOW;

    /** @test
     *
     * @dataProvider validStatusesProvider
     *
     * @param int $statusId
     */
    public function it_displays_an_order_with_a_valid_status(int $statusId)
    {
        $order = Order::factory()->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($statusId)->create();
        OrderDelivery::factory()->usingOrder($order)->create();
        $route = URL::route($this->routeName, $order);

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($order->getRouteKey(), $data['id']);
    }

    public function validStatusesProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, true],
            [Substatus::STATUS_CANCELED_ABORTED, true],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_COMPLETED_DONE, true],
        ];
    }

    /** @test
     *
     * @dataProvider invalidStatusesProvider
     *
     * @param int $statusId
     */
    public function it_should_not_display_an_order_with_an_invalid_status(int $statusId)
    {
        $order = Order::factory()->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($statusId)->create();

        $route = URL::route($this->routeName, $order);

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function invalidStatusesProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED],
        ];
    }
}
