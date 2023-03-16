<?php

namespace Tests\Unit\Http\Resources\Api\V4\Account\Cart\CartItem;

use App\Http\Resources\Api\V4\Account\Cart\CartItem\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ItemResource;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Part;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $cartItem = Mockery::mock(CartItem::class);
        $item     = Mockery::mock(Item::class);
        $part     = Mockery::mock(Part::class);

        $part->shouldReceive('getAttribute')->with('image')->once()->andReturnNull();
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnFalse();
        $part->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $part->shouldReceive('hiddenNumber')->withNoArgs()->once()->andReturn('xxxxxx');
        $part->shouldReceive('getAttribute')->with('type')->once()->andReturn('part type');
        $part->shouldReceive('getAttribute')->with('subtype')->once()->andReturn('part sub type');
        $part->shouldReceive('getAttribute')->with('brand')->once()->andReturn('part brand');
        $part->shouldReceive('getAttribute')->with('subcategory')->once()->andReturn('part sub category');

        $item->shouldReceive('getRouteKey')->withNoArgs()->times(2)->andReturn('item id');
        $item->shouldReceive('getAttribute')->with('type')->once()->andReturn('item type');
        $item->shouldReceive('getAttribute')->with('orderable')->once()->andReturn($part);
        $item->shouldReceive('isPart')->withNoArgs()->once()->andReturnTrue();
        $item->shouldReceive('isSupply')->withNoArgs()->once()->andReturnFalse();
        $item->shouldReceive('isCustomItem')->withNoArgs()->once()->andReturnFalse();

        $cartItem->shouldReceive('getRoutekey')->withNoArgs()->once()->andReturn($cartItemId = 'cart item id');
        $cartItem->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity = '3');
        $cartItem->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);

        $data = [
            'id'       => $cartItemId,
            'quantity' => $quantity,
            'item'     => new ItemResource($item, true),
        ];

        $resource = new BaseResource($cartItem);
        $response = $resource->resolve();

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
