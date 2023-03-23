<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Wishlist\ItemWishlist;

use App\Http\Resources\Api\V3\Account\Wishlist\ItemWishlist\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ItemResource;
use App\Models\Item;
use App\Models\ItemWishlist;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $wishList     = Wishlist::factory()->create();
        $item         = Item::factory()->create(['type' => 'supply']);
        $itemWishList = ItemWishlist::factory()->usingItem($item)->usingWishlist($wishList)->create();

        $resource = new BaseResource($itemWishList);
        $response = $resource->resolve();
        $data     = [
            'id'       => $itemWishList->getRouteKey(),
            'quantity' => $itemWishList->quantity,
            'item'     => new ItemResource($itemWishList->item),
        ];

        $this->assertEquals($data, $response);
        $this->jsonSchema(BaseResource::jsonSchema(), false, false);
    }
}
