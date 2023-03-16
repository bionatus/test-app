<?php

namespace Tests\Unit\Policies\Nova\Supply;

use App\Models\Item;
use App\Models\Supply;
use App\Policies\Nova\SupplyPolicy;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mockery;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function it_does_not_allows_to_delete_a_supply_that_is_in_an_order()
    {
        $orders = Mockery::mock(BelongsToMany::class);
        $orders->shouldReceive('count')->withNoArgs()->once()->andReturn(55);
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('orders')->withNoArgs()->once()->andReturn($orders);
        $supply = Mockery::mock(Supply::class);
        $supply->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $user = Mockery::mock(User::class);

        $policy = new SupplyPolicy();

        $this->assertFalse($policy->delete($user, $supply));
    }

    /** @test */
    public function it_allows_to_delete_a_supply_that_is_not_in_any_order()
    {
        $orders = Mockery::mock(BelongsToMany::class);
        $orders->shouldReceive('count')->withNoArgs()->once()->andReturn(0);
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('orders')->withNoArgs()->once()->andReturn($orders);
        $supply = Mockery::mock(Supply::class);
        $supply->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $user = Mockery::mock(User::class);

        $policy = new SupplyPolicy();

        $this->assertTrue($policy->delete($user, $supply));
    }
}
