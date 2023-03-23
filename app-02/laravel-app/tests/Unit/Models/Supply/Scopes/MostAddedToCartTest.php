<?php

namespace Tests\Unit\Models\Supply\Scopes;

use App\Models\CartSupplyCounter;
use App\Models\Supply;
use App\Models\Supply\Scopes\MostAddedToCart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MostAddedToCartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sorts_supplies_by_the_most_added_to_the_cart()
    {
        $user = User::factory()->create();

        $firstSupply  = Supply::factory()->visible()->create();
        $secondSupply = Supply::factory()->visible()->create();
        $thirdSupply  = Supply::factory()->visible()->create();

        CartSupplyCounter::factory()->usingSupply($firstSupply)->usingUser($user)->count(1)->create();
        CartSupplyCounter::factory()->usingSupply($secondSupply)->usingUser($user)->count(3)->create();
        CartSupplyCounter::factory()->usingSupply($thirdSupply)->usingUser($user)->count(2)->create();

        $expected = Collection::make([
            $secondSupply,
            $thirdSupply,
            $firstSupply,
        ]);

        $return = Supply::scoped(new MostAddedToCart())->get();
        $this->assertCount(3, $return);
        $return->each(function(Supply $supply) use ($expected) {
            /** @var Supply $expectedSupply */
            $expectedSupply = $expected->shift();
            $this->assertSame($expectedSupply->getKey(), $supply->getKey());
        });
    }
}
