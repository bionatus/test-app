<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\PublishedFavorite;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PublishedFavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_favorite_with_all_unpublished()
    {
        $user                = User::factory()->create();
        $favorites           = SupplierUser::factory()->usingUser($user)->count(3)->createQuietly()->pluck('supplier');
        $free                = Supplier::factory()->count(2)->createQuietly()->fresh();
        $notVisibleSuppliers = SupplierUser::factory()
            ->usingUser($user)
            ->count(2)
            ->notVisible()
            ->createQuietly()
            ->pluck('supplier');

        $tailSuppliers = $free->merge($notVisibleSuppliers);
        $sorted        = Supplier::scoped(new PublishedFavorite($user))->get();

        Collection::make([$favorites, $tailSuppliers])->each(function(
            Collection $collection
        ) use ($sorted) {
            $length = $collection->count();
            $chunk  = $sorted->splice(0, $length)->pluck(Supplier::keyName());
            $this->assertEqualsCanonicalizing($collection->pluck(Supplier::keyName())->toArray(), $chunk->toArray());
        });
    }

    /** @test */
    public function it_orders_by_favorite_and_published_at()
    {
        $user      = User::factory()->create();
        $supplier  = Supplier::factory()->createQuietly(['published_at' => Carbon::now()]);
        $free      = Supplier::factory()->count(2)->createQuietly()->fresh();
        $favorites = SupplierUser::factory()->usingUser($user)->count(3)->createQuietly()->pluck('supplier');

        SupplierUser::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $sorted = Supplier::scoped(new PublishedFavorite($user))->get();

        $favoritePublishedAt = Collection::make([$supplier]);
        Collection::make([$favoritePublishedAt, $favorites, $free])->each(function(Collection $collection) use ($sorted
        ) {
            $length = $collection->count();
            $chunk  = $sorted->splice(0, $length)->pluck(Supplier::keyName());
            $this->assertEqualsCanonicalizing($collection->pluck(Supplier::keyName())->toArray(), $chunk->toArray());
        });
    }
}
