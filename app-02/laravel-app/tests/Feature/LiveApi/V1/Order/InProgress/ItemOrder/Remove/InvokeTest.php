<?php

namespace Tests\Feature\LiveApi\V1\Order\InProgress\ItemOrder\Remove;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\ItemOrder\Removed;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\ItemOrder\RemoveController;
use App\Http\Resources\LiveApi\V1\Order\InProgress\ItemOrder\BaseResource;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\Supply;
use Auth;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see RemoveController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_ITEM_ORDER_REMOVE_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $itemOrder = ItemOrder::factory()->createQuietly();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => $itemOrder->order, RouteParameters::ITEM_ORDER => $itemOrder]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName,
            ['can:removeItemOrderInProgress,' . RouteParameters::ITEM_ORDER]);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_removes_an_item_order_from_an_approved_or_completed_order(int $substatusId)
    {
        Event::fake(Removed::class);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->create();
        Supply::factory()->create([Supply::keyName() => $itemOrder->item->getKey()]);
        $staff = Staff::factory()->usingSupplier($supplier)->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => $itemOrder->order, RouteParameters::ITEM_ORDER => $itemOrder]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'id'     => $itemOrder->getKey(),
            'status' => ItemOrder::STATUS_REMOVED,
        ]);
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
            [Substatus::STATUS_COMPLETED_DONE],
        ];
    }
}
