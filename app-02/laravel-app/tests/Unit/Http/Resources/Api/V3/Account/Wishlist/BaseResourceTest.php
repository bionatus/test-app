<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Wishlist;

use App\Http\Resources\Api\V3\Account\Wishlist\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('sum')->withAnyArgs()->once()->andReturn($wishlistItemsCount = 0);

        $wishlist = Mockery::mock(Wishlist::class);
        $wishlist->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'fake id');
        $wishlist->shouldReceive('itemWishlists')->withNoArgs()->once()->andReturn($hasMany);
        $wishlist->shouldReceive('getAttribute')->with('name')->once()->andReturn($nameWishlist = 'My first wishlist');

        $resource = new BaseResource($wishlist);

        $response = $resource->resolve();
        $data     = [
            'id'          => $id,
            'name'        => $nameWishlist,
            'total_items' => $wishlistItemsCount,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
