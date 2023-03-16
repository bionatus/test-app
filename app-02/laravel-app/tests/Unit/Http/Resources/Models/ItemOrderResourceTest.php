<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\ItemOrderResource;
use App\Models\ItemOrder;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class ItemOrderResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $id                = $this->faker->uuid;
        $quantity          = $this->faker->numberBetween(10);
        $quantityRequested = $this->faker->numberBetween(10);
        $price             = $this->faker->numberBetween(99999);
        $status            = ItemOrder::STATUS_PENDING;

        $itemOrder = Mockery::mock(ItemOrder::class);
        $itemOrder->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $itemOrder->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity);
        $itemOrder->shouldReceive('getAttribute')->with('quantity_requested')->once()->andReturn($quantityRequested);
        $itemOrder->shouldReceive('getAttribute')->with('price')->once()->andReturn($price);
        $itemOrder->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $itemOrder->shouldReceive('getAttribute')->with('supply_detail')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')->with('custom_detail')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')->with('generic_part_description')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')->with('initial_request')->once()->andReturnTrue();

        $resource = new ItemOrderResource($itemOrder);

        $response = $resource->resolve();

        $data = [
            'id'                       => $id,
            'quantity'                 => $quantity,
            'quantity_requested'       => $quantityRequested,
            'price'                    => $price,
            'status'                   => $status,
            'supply_detail'            => null,
            'custom_detail'            => null,
            'generic_part_description' => null,
            'initial_request'          => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemOrderResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_supply_detail()
    {
        $id                = $this->faker->uuid;
        $quantity          = $this->faker->numberBetween(10);
        $quantityRequested = $this->faker->numberBetween(10);
        $price             = $this->faker->numberBetween(99999);
        $status            = ItemOrder::STATUS_PENDING;
        $supplyDetail      = $this->faker->text(50);

        $itemOrder = Mockery::mock(ItemOrder::class);
        $itemOrder->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $itemOrder->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity);
        $itemOrder->shouldReceive('getAttribute')->with('quantity_requested')->once()->andReturn($quantityRequested);
        $itemOrder->shouldReceive('getAttribute')->with('price')->once()->andReturn($price);
        $itemOrder->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $itemOrder->shouldReceive('getAttribute')->with('supply_detail')->once()->andReturn($supplyDetail);
        $itemOrder->shouldReceive('getAttribute')->with('custom_detail')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')->with('generic_part_description')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')->with('initial_request')->once()->andReturnTrue();

        $resource = new ItemOrderResource($itemOrder);

        $response = $resource->resolve();

        $data = [
            'id'                       => $id,
            'quantity'                 => $quantity,
            'quantity_requested'       => $quantityRequested,
            'price'                    => $price,
            'status'                   => $status,
            'supply_detail'            => $supplyDetail,
            'custom_detail'            => null,
            'generic_part_description' => null,
            'initial_request'          => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemOrderResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_generic_part_description()
    {
        $id                     = $this->faker->uuid;
        $quantity               = $this->faker->numberBetween(10);
        $quantityRequested      = $this->faker->numberBetween(10);
        $price                  = $this->faker->numberBetween(99999);
        $status                 = ItemOrder::STATUS_PENDING;
        $genericPartDescription = $this->faker->text(255);

        $itemOrder = Mockery::mock(ItemOrder::class);
        $itemOrder->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $itemOrder->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity);
        $itemOrder->shouldReceive('getAttribute')->with('quantity_requested')->once()->andReturn($quantityRequested);
        $itemOrder->shouldReceive('getAttribute')->with('price')->once()->andReturn($price);
        $itemOrder->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $itemOrder->shouldReceive('getAttribute')->with('supply_detail')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')->with('custom_detail')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')
            ->with('generic_part_description')
            ->once()
            ->andReturn($genericPartDescription);
        $itemOrder->shouldReceive('getAttribute')->with('initial_request')->once()->andReturnTrue();

        $resource = new ItemOrderResource($itemOrder);

        $response = $resource->resolve();

        $data = [
            'id'                       => $id,
            'quantity'                 => $quantity,
            'quantity_requested'       => $quantityRequested,
            'price'                    => $price,
            'status'                   => $status,
            'supply_detail'            => null,
            'custom_detail'            => null,
            'generic_part_description' => $genericPartDescription,
            'initial_request'          => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemOrderResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_custom_detail()
    {
        $id                = $this->faker->uuid;
        $quantity          = $this->faker->numberBetween(10);
        $quantityRequested = $this->faker->numberBetween(10);
        $price             = $this->faker->numberBetween(99999);
        $status            = ItemOrder::STATUS_PENDING;
        $customDetail      = $this->faker->text(50);

        $itemOrder = Mockery::mock(ItemOrder::class);
        $itemOrder->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $itemOrder->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity);
        $itemOrder->shouldReceive('getAttribute')->with('quantity_requested')->once()->andReturn($quantityRequested);
        $itemOrder->shouldReceive('getAttribute')->with('price')->once()->andReturn($price);
        $itemOrder->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $itemOrder->shouldReceive('getAttribute')->with('supply_detail')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')->with('custom_detail')->once()->andReturn($customDetail);
        $itemOrder->shouldReceive('getAttribute')->with('generic_part_description')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')->with('initial_request')->once()->andReturnTrue();

        $resource = new ItemOrderResource($itemOrder);

        $response = $resource->resolve();

        $data = [
            'id'                       => $id,
            'quantity'                 => $quantity,
            'quantity_requested'       => $quantityRequested,
            'price'                    => $price,
            'status'                   => $status,
            'supply_detail'            => null,
            'custom_detail'            => $customDetail,
            'generic_part_description' => null,
            'initial_request'          => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemOrderResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
