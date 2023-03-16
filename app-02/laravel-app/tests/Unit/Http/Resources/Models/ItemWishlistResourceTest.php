<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ItemWishlistResource;
use App\Models\ItemWishList;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class ItemWishlistResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(ItemWishlistResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $id       = $this->faker->uuid;
        $quantity = $this->faker->numberBetween(10);

        $itemWishlist = Mockery::mock(ItemWishlist::class);
        $itemWishlist->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $itemWishlist->shouldReceive('getAttribute')->withArgs(['quantity'])->once()->andReturn($quantity);

        $resource = new ItemWishlistResource($itemWishlist);

        $response = $resource->resolve();

        $data = [
            'id'       => $id,
            'quantity' => $quantity,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemWishlistResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
