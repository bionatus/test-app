<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Order\ItemOrder;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\ItemOrderController;
use App\Http\Requests\LiveApi\V1\Order\ItemOrder\UpdateRequest;
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

/** @see ItemOrderController */
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

        $this->route = URL::route(RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_UPDATE,
            ['order' => $order, RouteParameters::ITEM_ORDER => $this->itemOrderPart]);

        $this->supplyRoute = URL::route(RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_UPDATE,
            ['order' => $order, RouteParameters::ITEM_ORDER => $this->itemOrderSupply]);

        $this->customItemRoute = URL::route(RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_UPDATE,
            ['order' => $order, RouteParameters::ITEM_ORDER => $this->itemOrderCustomItem]);

        Route::model(RouteParameters::ITEM_ORDER, ItemOrder::class);
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ITEM_ORDER, $this->itemOrderPart->getRouteKey())
            ->assertAuthorized();
    }

    /** @test */
    public function its_quantity_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 'da1234'],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_must_be_an_integer_than_greater_0()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 0],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => RequestKeys::QUANTITY,
                'min'       => 1,
            ]),
        ]);
    }

    /** @test */
    public function its_price_parameter_is_required()
    {

        $request = $this->formRequest($this->requestClass, [], ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PRICE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PRICE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_price_parameter_must_be_a_numeric()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PRICE => 'da1234'],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PRICE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PRICE);
        $request->assertValidationMessages([Lang::get('validation.numeric', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_price_parameter_must_be_a_numeric_of_than_greater_or_equals_to_0()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PRICE => -1],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PRICE]);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => RequestKeys::PRICE,
                'min'       => 0,
            ]),
        ]);
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
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS => ItemOrder::STATUS_SEE_BELOW_ITEM],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_supply_detail_parameter_must_be_a_string_when_item_is_a_supply()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLY_DETAIL => 1234],
            ['method' => 'patch', 'route' => $this->supplyRoute]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLY_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SUPPLY_DETAIL);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_custom_detail_parameter_must_be_not_present_when_the_item_is_a_supply()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CUSTOM_DETAIL => 'description custom item'],
            ['method' => 'patch', 'route' => $this->supplyRoute]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CUSTOM_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CUSTOM_DETAIL);
        $request->assertValidationMessages([
            Lang::get(':attribute is not allowed.', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_replacement_parameter_must_be_not_present_when_the_item_is_a_custom_item()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::REPLACEMENT => ['type' => 'generic', 'description' => 'description supply']],
            ['method' => 'patch', 'route' => $this->customItemRoute]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT);
        $request->assertValidationMessages([
            Lang::get(':attribute is not allowed.', ['attribute' => $attribute]),
        ]);
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
    public function its_supply_detail_parameter_must_be_not_present_when_the_item_is_a_part()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLY_DETAIL => 'description supply'],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLY_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SUPPLY_DETAIL);
        $request->assertValidationMessages([
            Lang::get(':attribute is not allowed.', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_custom_detail_parameter_must_be_not_present_when_the_item_is_a_part()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CUSTOM_DETAIL => 'description custom item'],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CUSTOM_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CUSTOM_DETAIL);
        $request->assertValidationMessages([
            Lang::get(':attribute is not allowed.', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_custom_detail_parameter_must_be_a_string_when_item_is_a_custom_item()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CUSTOM_DETAIL => 1234],
            ['method' => 'patch', 'route' => $this->customItemRoute]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CUSTOM_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CUSTOM_DETAIL);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */

    public function its_supply_detail_parameter_must_be_not_present_when_the_item_is_a_custom_item()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLY_DETAIL => 'description supply'],
            ['method' => 'patch', 'route' => $this->customItemRoute]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLY_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SUPPLY_DETAIL);
        $request->assertValidationMessages([
            Lang::get(':attribute is not allowed.', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_replacement_parameter_must_be_not_present_when_the_item_is_a_supply()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::REPLACEMENT => ['type' => 'generic', 'description' => 'description supply']],
            ['method' => 'patch', 'route' => $this->supplyRoute]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REPLACEMENT]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REPLACEMENT);
        $request->assertValidationMessages([
            Lang::get(':attribute is not allowed.', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data_for_supply()
    {
        $data    = [
            RequestKeys::QUANTITY      => 1,
            RequestKeys::PRICE         => 152.68,
            RequestKeys::STATUS        => ItemOrder::STATUS_AVAILABLE,
            RequestKeys::SUPPLY_DETAIL => 'supply detail',
        ];
        $request = $this->formRequest($this->requestClass, $data, ['method' => 'patch', 'route' => $this->supplyRoute]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_pass_on_valid_data_for_part_without_replacement()
    {
        $data    = [
            RequestKeys::QUANTITY    => 1,
            RequestKeys::PRICE       => 152.68,
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
            RequestKeys::QUANTITY    => 1,
            RequestKeys::PRICE       => 152.68,
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
            RequestKeys::QUANTITY    => 1,
            RequestKeys::PRICE       => 152.68,
            RequestKeys::STATUS      => ItemOrder::STATUS_AVAILABLE,
            RequestKeys::REPLACEMENT => ['type' => 'generic', 'description' => 'description replacement'],
        ];
        $request = $this->formRequest($this->requestClass, $data, ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_pass_on_valid_data_for_custom_item()
    {
        $data    = [
            RequestKeys::QUANTITY      => 1,
            RequestKeys::PRICE         => 152.68,
            RequestKeys::STATUS        => ItemOrder::STATUS_AVAILABLE,
            RequestKeys::CUSTOM_DETAIL => 'custom item detail',
        ];
        $request = $this->formRequest($this->requestClass, $data,
            ['method' => 'patch', 'route' => $this->customItemRoute]);

        $request->assertValidationPassed();
    }
}
