<?php

namespace Tests\Unit\Http\Requests\Api\V4\Order\ItemOrder\ExtraItem;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V4\Order\ItemOrder\ExtraItemController;
use App\Http\Requests\Api\V4\Order\ItemOrder\ExtraItem\StoreRequest;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\Part;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see ExtraItemController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_items_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS])]);
    }

    /** @test */
    public function its_items_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS]);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => RequestKeys::ITEMS])]);
    }

    /** @test */
    public function each_item_in_items_is_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [[]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0']);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS . '.0']),
        ]);
    }

    /** @test */
    public function each_item_uuid_in_items_is_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [[]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS . '.0.uuid']),
        ]);
    }

    /** @test */
    public function each_item_uuid_in_items_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['uuid' => 1]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::ITEMS . '.0.uuid']),
        ]);
    }

    /** @test */
    public function each_item_uuid_in_items_must_exist()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['uuid' => 'invalid']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages(['The item should exist and be type supply or custom item added by the technician.']);
    }

    /** @test */
    public function each_item_uuid_in_items_should_not_be_a_part()
    {
        $part    = Part::factory()->create();
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::ITEMS => [['uuid' => $part->item->getRouteKey()]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages(['The item should exist and be type supply or custom item added by the technician.']);
    }

    /** @test */
    public function each_item_uuid_in_items_should_not_be_a_supplier_custom_item()
    {
        $part    = CustomItem::factory()->create(['creator_type' => 'supplier']);
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::ITEMS => [['uuid' => $part->item->getRouteKey()]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages(['The item should exist and be type supply or custom item added by the technician.']);
    }

    /** @test */
    public function each_item_quantity_in_items_is_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [[]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS . '.0.quantity']),
        ]);
    }

    /** @test */
    public function each_item_quantity_in_items_must_be_a_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['quantity' => 'invalid']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages([
            Lang::get('validation.integer', ['attribute' => RequestKeys::ITEMS . '.0.quantity']),
        ]);
    }

    /** @test */
    public function each_item_quantity_in_items_must_be_minimum_one()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['quantity' => 0]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', ['attribute' => RequestKeys::ITEMS . '.0.quantity', 'min' => 1]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_values()
    {
        $item    = Item::factory()->create();
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::ITEMS => [
                [
                    'uuid'     => $item->getRouteKey(),
                    'quantity' => 1,
                ],
            ],
        ]);

        $request->assertValidationPassed();
    }
}
