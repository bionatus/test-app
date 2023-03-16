<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\Order\Scopes\BySearchString;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySearchStringTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_order_by_a_non_empty_string()
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create(['first_name' => 'User SEARCH first name']);
        $lastUser  = User::factory()->create(['last_name' => 'User SEARCH last name']);
        $company   = Company::factory()->create(['name' => 'Company SEARCH name']);

        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();
        Order::factory()->usingUser($user)->createQuietly();
        Order::factory()->usingUser($otherUser)->createQuietly();
        Order::factory()->usingUser($lastUser)->createQuietly();
        Order::factory()->createQuietly(['name' => 'Order SEARCH name']);
        Order::factory()->createQuietly(['bid_number' => 'Order SEARCH bid_number']);
        Order::factory()->count(3)->createQuietly();

        $orders = Order::scoped(new BySearchString('SEARCH'))->get();

        $this->assertCount(5, $orders);
    }

    /** @test */
    public function it_filters_orders_by_an_empty_string()
    {
        Order::factory()->count(3)->createQuietly();

        $orders = Order::scoped(new BySearchString(''))->get();

        $this->assertCount(3, $orders);
    }
}
