<?php

namespace Tests\Feature\LiveApi\V2\Order\ItemOrder\Part;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\ItemOrder\PartController;
use App\Http\Requests\LiveApi\V2\Order\ItemOrder\Part\UpdateRequest;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\Part\BaseResource;
use App\Models\AirFilter;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\SingleReplacement;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PartController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_PART_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $part      = Part::factory()->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:updateItems,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_updates_the_item_order_that_uses_a_part()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pending()->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $status = ItemOrder::STATUS_AVAILABLE;

        $response = $this->patch($route, [
            RequestKeys::STATUS      => $status,
            RequestKeys::REPLACEMENT => null,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'id'                       => $itemOrder->getKey(),
            'uuid'                     => $itemOrder->uuid,
            'order_id'                 => $order->getKey(),
            'item_id'                  => $part->item->getKey(),
            'status'                   => $status,
            'replacement_id'           => null,
            'generic_part_description' => null,
        ]);
    }

    /** @test */
    public function it_updates_the_item_order_that_uses_a_part_with_a_generic_replacement()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pending()->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $status      = ItemOrder::STATUS_AVAILABLE;
        $description = 'fake generic part';

        $response = $this->patch($route, [
            RequestKeys::STATUS      => $status,
            RequestKeys::REPLACEMENT => ['type' => 'generic', 'description' => $description],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'id'                       => $itemOrder->getKey(),
            'uuid'                     => $itemOrder->uuid,
            'order_id'                 => $order->getKey(),
            'item_id'                  => $part->item->getKey(),
            'status'                   => $status,
            'replacement_id'           => null,
            'generic_part_description' => $description,
        ]);
    }

    /** @test */
    public function it_updates_the_item_order_that_uses_a_part_with_a_part_replacement()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pending()->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $replacement       = Replacement::factory()->usingPart($part)->create();
        $singleReplacement = SingleReplacement::factory()->usingReplacement($replacement)->create();
        $itemOrder         = ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($part->item)
            ->usingReplacement($singleReplacement->replacement)
            ->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $status = ItemOrder::STATUS_AVAILABLE;
        $id     = $singleReplacement->replacement->uuid;

        $response = $this->patch($route, [
            RequestKeys::STATUS      => $status,
            RequestKeys::REPLACEMENT => ['type' => 'replacement', 'id' => $id],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'id'                       => $itemOrder->getKey(),
            'uuid'                     => $itemOrder->uuid,
            'order_id'                 => $order->getKey(),
            'item_id'                  => $part->item->getKey(),
            'status'                   => $status,
            'replacement_id'           => $replacement->getKey(),
            'generic_part_description' => null,
        ]);
    }

    /** @test */
    public function it_does_not_update_an_item_order_that_belongs_to_another_order()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pending()->create();
        $anotherOrder = Order::factory()->usingSupplier($staff->supplier)->pending()->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($anotherOrder)->usingItem($part->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $status = ItemOrder::STATUS_AVAILABLE;

        $response = $this->patch($route, [
            RequestKeys::STATUS      => $status,
            RequestKeys::REPLACEMENT => null,
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
