<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Str;

class WishlistTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Wishlist::tableName(), [
            'id',
            'uuid',
            'user_id',
            'name',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $wishlist = Wishlist::factory()->createQuietly(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($wishlist->uuid, $wishlist->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $wishlist = Wishlist::factory()->make(['uuid' => null]);
        $wishlist->save();

        $this->assertNotNull($wishlist->uuid);
    }

    /** @test */
    public function it_knows_if_a_user_is_its_owner()
    {
        $notOwner = User::factory()->create();
        $owner    = User::factory()->create();

        $wishlist = Wishlist::factory()->usingUser($owner)->create();

        $this->assertFalse($wishlist->isOwner($notOwner));
        $this->assertTrue($wishlist->isOwner($owner));
    }
}
