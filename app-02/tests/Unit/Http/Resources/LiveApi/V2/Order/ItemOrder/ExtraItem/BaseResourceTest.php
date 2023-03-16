<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Order\ItemOrder\ExtraItem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\ExtraItem\BaseResource;
use App\Http\Resources\Models\CustomItemResource;
use App\Http\Resources\Models\SupplyResource;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Supply;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_supply()
    {
        $temOrderId = $this->faker->uuid;
        $itemId     = $this->faker->uuid;
        $status     = ItemOrder::STATUS_PENDING;

        $supply = Mockery::mock(Supply::class);
        $item   = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($itemId);
        $item->shouldReceive('getAttribute')->with('type')->twice()->andReturn(Item::TYPE_SUPPLY);
        $item->shouldReceive('getAttribute')->with('orderable')->once()->andReturn($supply);

        $supply->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $supply->shouldReceive('getAttribute')->with('name')->once()->andReturn('name');
        $supply->shouldReceive('getAttribute')->with('internal_name')->once()->andReturn('internal name');
        $supply->shouldReceive('getAttribute')->with('sort')->once()->andReturnNull();
        $supply->shouldReceive('getCategoryMedia')->withNoArgs()->once()->andReturnNull();

        $itemOrder = Mockery::mock(ItemOrder::class);
        $itemOrder->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($temOrderId);
        $itemOrder->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $itemOrder->shouldReceive('getAttribute')->with('generic_part_description')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')
            ->with('supply_detail')
            ->once()
            ->andReturn($supplyDetail = 'supply_detail');
        $itemOrder->shouldReceive('getAttribute')
            ->with('custom_detail')
            ->once()
            ->andReturn($customDetail = 'custom_detail');
        $itemOrder->shouldReceive('getAttribute')->with('price')->once()->andReturn($price = 100);
        $itemOrder->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity = 2);
        $itemOrder->shouldReceive('getAttribute')
            ->with('quantity_requested')
            ->once()
            ->andReturn($quantityRequested = 3);
        $itemOrder->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $itemOrder->shouldReceive('getAttribute')->with('initial_request')->once()->andReturnTrue();

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $temOrderId,
            'quantity'                 => $quantity,
            'quantity_requested'       => $quantityRequested,
            'price'                    => $price,
            'status'                   => $status,
            'supply_detail'            => $supplyDetail,
            'custom_detail'            => $customDetail,
            'generic_part_description' => null,
            'item'                     => new SupplyResource($supply),
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_custom_item()
    {
        $temOrderId = $this->faker->uuid;
        $itemId     = $this->faker->uuid;
        $status     = ItemOrder::STATUS_PENDING;

        $customItem = Mockery::mock(CustomItem::class);
        $item       = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($itemId);
        $item->shouldReceive('getAttribute')->with('type')->twice()->andReturn(Item::TYPE_CUSTOM_ITEM);
        $item->shouldReceive('getAttribute')->with('orderable')->once()->andReturn($customItem);

        $customItem->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $customItem->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $customItem->shouldReceive('getAttribute')->withArgs(['creator_type'])->once()->andReturn('user');

        $itemOrder = Mockery::mock(ItemOrder::class);

        $itemOrder->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($temOrderId);
        $itemOrder->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $itemOrder->shouldReceive('getAttribute')->with('generic_part_description')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')
            ->with('supply_detail')
            ->once()
            ->andReturn($supplyDetail = 'supply_detail');
        $itemOrder->shouldReceive('getAttribute')
            ->with('custom_detail')
            ->once()
            ->andReturn($customDetail = 'custom_detail');
        $itemOrder->shouldReceive('getAttribute')->with('price')->once()->andReturn($price = 100);
        $itemOrder->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity = 2);
        $itemOrder->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $itemOrder->shouldReceive('getAttribute')
            ->with('quantity_requested')
            ->once()
            ->andReturn($quantityRequested = 3);
        $itemOrder->shouldReceive('getAttribute')->with('initial_request')->once()->andReturnTrue();

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $temOrderId,
            'quantity'                 => $quantity,
            'quantity_requested'       => $quantityRequested,
            'price'                    => $price,
            'status'                   => $status,
            'supply_detail'            => $supplyDetail,
            'custom_detail'            => $customDetail,
            'generic_part_description' => null,
            'item'                     => new CustomItemResource($customItem),
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
