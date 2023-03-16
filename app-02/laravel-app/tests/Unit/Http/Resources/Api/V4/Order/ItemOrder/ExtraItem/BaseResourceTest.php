<?php

namespace Tests\Unit\Http\Resources\Api\V4\Order\ItemOrder\ExtraItem;

use App\Http\Resources\Api\V4\Order\ItemOrder\ExtraItem\BaseResource;
use App\Http\Resources\Api\V4\Order\ItemOrder\ExtraItem\ItemResource;
use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Supply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields_with_supplies()
    {
        $supply    = Supply::factory()->create();
        $order     = Order::factory()->approved()->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($supply->item)->createQuietly();

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $itemOrder->getRouteKey(),
            'quantity'                 => $itemOrder->quantity,
            'quantity_requested'       => $itemOrder->quantity_requested,
            'price'                    => $itemOrder->price,
            'status'                   => $itemOrder->status,
            'supply_detail'            => $itemOrder->supply_detail,
            'custom_detail'            => $itemOrder->custom_detail,
            'generic_part_description' => $itemOrder->generic_part_description,
            'item'                     => new ItemResource($itemOrder->item),
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_custom_item()
    {
        $customItem = CustomItem::factory()->create();
        $order      = Order::factory()->completed()->create();
        $itemOrder  = ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->createQuietly();

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $itemOrder->getRouteKey(),
            'quantity'                 => $itemOrder->quantity,
            'quantity_requested'       => $itemOrder->quantity_requested,
            'price'                    => $itemOrder->price,
            'status'                   => $itemOrder->status,
            'supply_detail'            => $itemOrder->supply_detail,
            'custom_detail'            => $itemOrder->custom_detail,
            'generic_part_description' => $itemOrder->generic_part_description,
            'item'                     => new ItemResource($itemOrder->item),
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
