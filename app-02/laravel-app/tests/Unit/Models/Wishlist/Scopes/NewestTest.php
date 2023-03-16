<?php

namespace Tests\Unit\Models\Wishlist\Scopes;

use App\Models\Wishlist;
use App\Models\Wishlist\Scopes\Newest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class NewestTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_sorts_by_newest()
    {
        $wishlists = Collection::make([
            Wishlist::factory()->createQuietly(['created_at' => $this->faker->dateTimeBetween('-2 day')]),
            Wishlist::factory()->createQuietly(['created_at' => $this->faker->dateTimeBetween('-1 day')]),
            Wishlist::factory()->createQuietly(['created_at' => $this->faker->dateTimeBetween('-3 day')]),
        ])->sortByDesc('created_at');

        $sorted = Wishlist::scoped(new Newest())->get();

        $sorted->each(function(Wishlist $wishlist) use ($wishlists) {
            $this->assertSame($wishlists->shift()->getKey(), $wishlist->getKey());
        });
    }
}
