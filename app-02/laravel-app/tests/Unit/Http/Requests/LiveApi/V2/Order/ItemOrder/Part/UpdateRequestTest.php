<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Order\ItemOrder\Part;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\ItemOrder\PartController;
use App\Http\Requests\LiveApi\V2\Order\ItemOrder\Part\UpdateRequest;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Replacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Lang;
use Route;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see PartController */
class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected string  $requestClass = UpdateRequest::class;
    private ItemOrder $itemOrderPart;
    private ItemOrder $itemOrderSupply;
    private ItemOrder $itemOrderCustomItem;
    private string    $route;
    private string    $supplyRoute;
    private string    $customItemRoute;

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::PART_ITEM_ORDER, $this->itemOrderPart->getRouteKey())
            ->assertAuthorized();
    }

    /** @test */
    public function its_status_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_status_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS => 1234],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_status_parameter_must_be_a_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS => ItemOrder::STATUS_PENDING],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_replacement_parameter_must_be_present_when_item_is_a_part()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT);
        $request->assertValidationMessages([Lang::get('validation.present', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_replacement_parameter_can_be_null_when_item_is_a_part()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REPLACEMENT => null],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationErrorsMissing([RequestKeys::REPLACEMENT]);
        $request->assertValidationErrorsMissing([RequestKeys::REPLACEMENT . '.type']);
    }

    /** @test */
    public function its_replacement_parameter_must_be_array_when_item_is_a_part()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REPLACEMENT => 'test is not array'],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_replacement_type_parameter_is_required_when_replacement_is_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REPLACEMENT => ['test' => 'test type']],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT . '.type']);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT . '.type');
        $request->assertValidationMessages([
            Lang::get('validation.required_unless',
                ['attribute' => $attribute, 'other' => RequestKeys::REPLACEMENT, 'values' => 'null']),
        ]);
    }

    /** @test */
    public function its_replacement_type_parameter_is_a_correct_option_when_replacement_is_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REPLACEMENT => ['type' => 'another option']],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT . '.type']);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT . '.type');
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_replacement_description_parameter_is_required_when_replacement_type_is_generic()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REPLACEMENT => ['type' => 'generic']],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT . '.description']);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT . '.description');
        $request->assertValidationMessages([
            Lang::get('validation.required_if',
                ['attribute' => $attribute, 'other' => RequestKeys::REPLACEMENT . '.type', 'value' => 'generic']),
        ]);
    }

    /** @test */
    public function its_replacement_id_parameter_is_required_when_replacement_type_is_replacement()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REPLACEMENT => ['type' => 'replacement']],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT . '.id']);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT . '.id');
        $request->assertValidationMessages([
            Lang::get('validation.required_if',
                ['attribute' => $attribute, 'other' => RequestKeys::REPLACEMENT . '.type', 'value' => 'replacement']),
        ]);
    }

    /** @test */
    public function its_replacement_id_parameter_must_be_exist_in_database()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::REPLACEMENT => ['type' => 'replacement', 'id' => '4e829f53-1c37']],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT . '.id']);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT . '.id');
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_replacement_id_parameter_must_have_a_relation_with_the_order_item()
    {
        $replacement = Replacement::factory()->create()->refresh();

        $request = $this->formRequest($this->requestClass,
            [RequestKeys::REPLACEMENT => ['type' => 'replacement', 'id' => $replacement->getRouteKey()]],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT . '.id']);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT . '.id');
        $request->assertValidationMessages([
            Str::replace(':attribute', $attribute, 'This :attribute is not valid to replace the item'),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data_for_part_without_replacement()
    {
        $data    = [
            RequestKeys::STATUS      => ItemOrder::STATUS_AVAILABLE,
            RequestKeys::REPLACEMENT => null,
        ];
        $request = $this->formRequest($this->requestClass, $data, ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_pass_on_valid_data_for_part_with_replacement()
    {
        $part        = $this->itemOrderPart->item->part;
        $replacement = Replacement::factory()->usingPart($part)->create();
        $data        = [
            RequestKeys::STATUS      => ItemOrder::STATUS_AVAILABLE,
            RequestKeys::REPLACEMENT => ['type' => 'replacement', 'id' => $replacement->uuid],
        ];
        $request     = $this->formRequest($this->requestClass, $data, ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_pass_on_valid_data_for_part_with_generic_replacement()
    {
        $data    = [
            RequestKeys::STATUS      => ItemOrder::STATUS_AVAILABLE,
            RequestKeys::REPLACEMENT => ['type' => 'generic', 'description' => 'description replacement'],
        ];
        $request = $this->formRequest($this->requestClass, $data, ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationPassed();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $part                      = Part::factory()->functional()->create();
        $order                     = Order::factory()->createQuietly();
        $this->itemOrderPart       = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();
        $this->itemOrderSupply     = ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem(Item::factory()->create())
            ->create();
        $this->itemOrderCustomItem = ItemOrder::factory()->usingOrder($order)->usingItem(Item::factory()
            ->customItem()
            ->create())->create();

        $this->route = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_PART_UPDATE,
            ['order' => $order, RouteParameters::PART_ITEM_ORDER => $this->itemOrderPart]);

        $this->supplyRoute = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_PART_UPDATE,
            ['order' => $order, RouteParameters::PART_ITEM_ORDER => $this->itemOrderSupply]);

        $this->customItemRoute = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_PART_UPDATE,
            ['order' => $order, RouteParameters::PART_ITEM_ORDER => $this->itemOrderCustomItem]);

        Route::model(RouteParameters::PART_ITEM_ORDER, ItemOrder::class);
    }
}
