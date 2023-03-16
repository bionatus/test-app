<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Order\InProgress\ItemOrder;

use App\Http\Resources\LiveApi\V1\Order\InProgress\ItemOrder\BaseResource;
use App\Http\Resources\Models\GenericReplacementResource;
use App\Http\Resources\Models\ItemResource;
use App\Http\Resources\Models\ReplacementResource;
use App\Models\CustomItem;
use App\Models\GroupedReplacement;
use App\Models\ItemOrder;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\SingleReplacement;
use App\Models\Supply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields_with_part_and_without_replacement()
    {
        $part      = Part::factory()->create();
        $itemOrder = ItemOrder::factory()->usingItem($part->item)->createQuietly();

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
            'replacement'              => null,
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_part_and_single_replacement()
    {
        $part        = Part::factory()->create();
        $replacement = Replacement::factory()->usingPart($part)->create();
        SingleReplacement::factory()->usingReplacement($replacement)->create();
        $itemOrder = ItemOrder::factory()->usingItem($part->item)->usingReplacement($replacement)->createQuietly();

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
            'replacement'              => new ReplacementResource($replacement->fresh()),
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_part_and_grouped_replacement()
    {
        $part = Part::factory()->create();

        $replacement = Replacement::factory()->grouped()->usingPart($part)->create();
        GroupedReplacement::factory()->usingReplacement($replacement)->create();
        $itemOrder = ItemOrder::factory()->usingItem($part->item)->usingReplacement($replacement)->createQuietly();

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
            'replacement'              => new ReplacementResource($replacement->fresh()),
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_part_and_generic_replacement()
    {
        $part = Part::factory()->create();

        $itemOrder = ItemOrder::factory()->usingItem($part->item)->createQuietly([
            'generic_part_description' => 'Fake generic part',
        ]);

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
            'replacement'              => new GenericReplacementResource($itemOrder->generic_part_description),
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_supply()
    {
        $supply    = Supply::factory()->create();
        $itemOrder = ItemOrder::factory()->usingItem($supply->item)->createQuietly();

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
            'replacement'              => null,
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
        $itemOrder  = ItemOrder::factory()->usingItem($customItem->item)->createQuietly();

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
            'replacement'              => null,
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
