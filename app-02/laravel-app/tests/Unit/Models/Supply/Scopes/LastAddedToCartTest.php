<?php

namespace Tests\Unit\Models\Supply\Scopes;

use App\Models\CartSupplyCounter;
use App\Models\Supply;
use App\Models\Supply\Scopes\LastAddedToCart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LastAddedToCartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sorts_by_the_latest_supplies_added_to_the_cart()
    {
        $user        = User::factory()->create();
        $supply      = Supply::factory()->create();
        $otherSupply = Supply::factory()->create();

        $lastCartSupplyCounter = CartSupplyCounter::factory()
            ->usingSupply($supply)
            ->usingUser($user)
            ->create(['created_at' => Carbon::now()->subDay()]);
        CartSupplyCounter::factory()->usingSupply($supply)->usingUser($user)->create([
            'created_at' => Carbon::now()->subDays(2),
        ]);
        CartSupplyCounter::factory()->usingSupply($supply)->usingUser($user)->create([
            'created_at' => Carbon::now()->subDay(),
        ]);
        $firstCartSupplyCounter = CartSupplyCounter::factory()
            ->usingSupply($otherSupply)
            ->usingUser($user)
            ->create(['created_at' => Carbon::now()]);

        $expected = Collection::make([$firstCartSupplyCounter->supply, $lastCartSupplyCounter->supply]);

        $return = Supply::scoped(new LastAddedToCart())->get();

        $this->assertCount(2, $return);
        $return->each(function(Supply $supply) use ($expected) {
            /** @var Supply $expectedSupply */
            $expectedSupply = $expected->shift();
            $this->assertSame($expectedSupply->getKey(), $supply->getKey());
        });
    }
}
