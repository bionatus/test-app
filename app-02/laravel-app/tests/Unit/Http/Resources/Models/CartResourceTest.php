<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CartResource;
use App\Models\Cart;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class CartResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(CartResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $createdAt = $this->faker->date('Y-m-d H:i:s');
        $cart      = Mockery::mock(Cart::class);

        $cart->shouldReceive('getAttribute')->withArgs(['created_at'])->once()->andReturn($createdAt);

        $resource = new CartResource($cart);
        $response = $resource->resolve();

        $data = [
            'created_at' => $createdAt,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CartResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
