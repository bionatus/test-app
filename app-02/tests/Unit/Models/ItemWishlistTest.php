<?php

namespace Tests\Unit\Models;

use App\Models\ItemWishlist;
use Illuminate\Support\Str;

class ItemWishlistTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ItemWishlist::tableName(), [
            'id',
            'uuid',
            'item_id',
            'wishlist_id',
            'quantity',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $itemWishlist = ItemWishlist::factory()->createQuietly(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($itemWishlist->uuid, $itemWishlist->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $itemWishlist = ItemWishlist::factory()->make(['uuid' => null]);
        $itemWishlist->save();

        $this->assertNotNull($itemWishlist->uuid);
    }
}
