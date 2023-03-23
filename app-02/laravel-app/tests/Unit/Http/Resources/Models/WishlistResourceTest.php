<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class WishlistResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(WishlistResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $wishlist = Mockery::mock(Wishlist::class);
        $wishlist->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'fake id');
        $wishlist->shouldReceive('getAttribute')->with('name')->once()->andReturn($nameWishlist = 'My first wishlist');

        $resource = new WishlistResource($wishlist);

        $response = $resource->resolve();
        $data     = [
            'id'   => $id,
            'name' => $nameWishlist,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(WishlistResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
