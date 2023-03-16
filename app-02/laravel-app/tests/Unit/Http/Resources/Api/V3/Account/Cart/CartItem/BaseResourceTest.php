<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Cart\CartItem;

use App\Http\Resources\Api\V3\Account\Cart\CartItem\BaseResource;
use App\Http\Resources\Models\ItemResource;
use App\Models\CartItem;
use App\Models\CustomItem;
use App\Models\Part;
use App\Models\Supply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields_with_part()
    {
        $part     = Part::factory()->create();
        $cartItem = CartItem::factory()->usingItem($part->item)->create();

        $resource = new BaseResource($cartItem);
        $response = $resource->resolve();

        $data = [
            'id'       => $cartItem->getRouteKey(),
            'quantity' => $cartItem->quantity,
            'item'     => new ItemResource($cartItem->item),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_supply()
    {
        $supply   = Supply::factory()->create();
        $cartItem = CartItem::factory()->usingItem($supply->item)->create();

        $resource = new BaseResource($cartItem);
        $response = $resource->resolve();

        $data = [
            'id'       => $cartItem->getRouteKey(),
            'quantity' => $cartItem->quantity,
            'item'     => new ItemResource($cartItem->item),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_custom_item()
    {
        $customItem = CustomItem::factory()->create();
        $cartItem   = CartItem::factory()->usingItem($customItem->item)->create();

        $resource = new BaseResource($cartItem);
        $response = $resource->resolve();

        $data = [
            'id'       => $cartItem->getRouteKey(),
            'quantity' => $cartItem->quantity,
            'item'     => new ItemResource($cartItem->item),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
