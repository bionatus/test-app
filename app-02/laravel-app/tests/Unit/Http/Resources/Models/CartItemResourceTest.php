<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\CartItemResource;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class CartItemResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $id       = $this->faker->uuid;
        $quantity = $this->faker->numberBetween(10);

        $itemOrder = Mockery::mock(CartItem::class);
        $itemOrder->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $itemOrder->shouldReceive('getAttribute')->withArgs(['quantity'])->once()->andReturn($quantity);

        $resource = new CartItemResource($itemOrder);

        $response = $resource->resolve();

        $data = [
            'id'       => $id,
            'quantity' => $quantity,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CartItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
