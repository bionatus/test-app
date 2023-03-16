<?php

namespace Tests\Feature\LiveApi\V2\Order\ItemOrder\CustomItem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\ItemOrder\CustomItemController;
use App\Http\Requests\LiveApi\V2\Order\ItemOrder\CustomItem\StoreRequest;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\ExtraItem\BaseResource;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Scopes\ByName;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CustomItemController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_CUSTOM_ITEM_STORE;

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
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:read,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_creates_a_custom_item()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->pending()->usingSupplier($supplier)->create();
        $name     = 'Custom item name';
        $quantity = 3;

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::NAME     => $name,
            RequestKeys::QUANTITY => $quantity,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(CustomItem::tableName(), [
            'name'         => $name,
            'creator_id'   => $staff->supplier->getKey(),
            'creator_type' => Supplier::MORPH_ALIAS,
        ]);

        $customItem = CustomItem::scoped(new ByName($name))->first();

        $this->assertDatabaseHas(Item::tableName(), ['id' => $customItem->id]);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
                'order_id' => $order->id,
                'item_id'  => $customItem->id,
                'quantity' => $quantity,
                'status'   => ItemOrder::STATUS_AVAILABLE,
            ]);
    }
}
