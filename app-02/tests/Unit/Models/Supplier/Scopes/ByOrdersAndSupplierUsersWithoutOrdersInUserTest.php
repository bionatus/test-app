<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Order;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByOrdersAndSupplierUsersWithoutOrdersInUser;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ByOrdersAndSupplierUsersWithoutOrdersInUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_first_the_suppliers_with_orders_and_second_the_common_suppliers_with_pubnub_channel()
    {
        $user      = User::factory()->create();
        $orders    = Order::factory()->usingUser($user)->count(2)->sequence(function(Sequence $sequence) {
            return ['updated_at' => Carbon::now()->subSeconds($sequence->index)];
        })->createQuietly();
        $suppliers = Supplier::factory()->published()->count(2)->createQuietly();

        $suppliers->each(function(Supplier $supplier, int $index) use ($user) {
            SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();
            PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier)->create([
                'created_at' => Carbon::now()
                    ->subMinutes($index),
            ]);
        });

        $expectedSupplierKeys = [
            ...$orders->pluck('supplier.' . Supplier::keyName()),
            ...$suppliers->pluck(Supplier::keyName()),
        ];

        $filteredSuppliers    = Supplier::scoped(new ByOrdersAndSupplierUsersWithoutOrdersInUser($user))->get();
        $filteredSupplierKeys = $filteredSuppliers->pluck(Supplier::keyName())->toArray();

        $this->assertEquals($expectedSupplierKeys, $filteredSupplierKeys);
    }

    /** @test */
    public function it_returns_the_suppliers_with_orders_ordered_by_order_updated_at()
    {
        $now    = Carbon::now();
        $user   = User::factory()->create();
        $order1 = Order::factory()->usingUser($user)->createQuietly(['updated_at' => $now->clone()->subSeconds(30)]);
        $order2 = Order::factory()->usingUser($user)->createQuietly(['updated_at' => $now->clone()->subSeconds(20)]);
        $order3 = Order::factory()->usingUser($user)->createQuietly(['updated_at' => $now->clone()->subSeconds(10)]);
        $order4 = Order::factory()->usingUser($user)->createQuietly(['updated_at' => $now->clone()->subSeconds(40)]);
        Order::factory()->count(3)->createQuietly();

        $expectedSupplierKeys = [
            $order3->supplier->getKey(),
            $order2->supplier->getKey(),
            $order1->supplier->getKey(),
            $order4->supplier->getKey(),
        ];

        $filteredSuppliers    = Supplier::scoped(new ByOrdersAndSupplierUsersWithoutOrdersInUser($user))->get();
        $filteredSupplierKeys = $filteredSuppliers->pluck(Supplier::keyName())->toArray();

        $this->assertEquals($expectedSupplierKeys, $filteredSupplierKeys);
    }

    /** @test */
    public function it_returns_the_common_suppliers_with_pubnub_channel_ordered_by_pubnub_channel_created_at()
    {
        $now       = Carbon::now();
        $user      = User::factory()->create();
        $supplier1 = Supplier::factory()->published()->createQuietly();
        $supplier2 = Supplier::factory()->published()->createQuietly();
        $supplier3 = Supplier::factory()->published()->createQuietly();
        $supplier4 = Supplier::factory()->published()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier1)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier2)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier3)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier4)->create();
        PubnubChannel::factory()->usingSupplier($supplier1)->usingUser($user)->create([
            'created_at' => $now->clone()->subSeconds(30),
        ]);
        PubnubChannel::factory()->usingSupplier($supplier2)->usingUser($user)->create([
            'created_at' => $now->clone()->subSeconds(20),
        ]);
        PubnubChannel::factory()->usingSupplier($supplier3)->usingUser($user)->create([
            'created_at' => $now->clone()->subSeconds(10),
        ]);
        PubnubChannel::factory()->usingSupplier($supplier4)->usingUser($user)->create([
            'created_at' => $now->clone()->subSeconds(40),
        ]);

        $expectedSupplierKeys = [
            $supplier3->getKey(),
            $supplier2->getKey(),
            $supplier1->getKey(),
            $supplier4->getKey(),
        ];

        $filteredSuppliers    = Supplier::scoped(new ByOrdersAndSupplierUsersWithoutOrdersInUser($user))->get();
        $filteredSupplierKeys = $filteredSuppliers->pluck(Supplier::keyName())->toArray();

        $this->assertEquals($expectedSupplierKeys, $filteredSupplierKeys);
    }

    /** @test */
    public function it_returns_only_the_suppliers_who_have_orders_with_the_user()
    {
        $user   = User::factory()->create();
        $orders = Order::factory()->count(3)->usingUser($user)->sequence(function(Sequence $sequence) {
            return ['updated_at' => Carbon::now()->subSeconds($sequence->index)];
        })->createQuietly();
        Order::factory()->count(6)->createQuietly();

        $expectedSupplierKeys = $orders->pluck('supplier.' . Supplier::keyName())->toArray();

        $filteredSuppliers    = Supplier::scoped(new ByOrdersAndSupplierUsersWithoutOrdersInUser($user))->get();
        $filteredSupplierKeys = $filteredSuppliers->pluck(Supplier::keyName())->toArray();

        $this->assertEquals($expectedSupplierKeys, $filteredSupplierKeys);
    }

    /** @test */
    public function it_return_the_supplier_once_if_it_has_multiple_orders_with_the_user()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->count(3)->create();
        Order::factory()->usingUser($user)->count(2)->createQuietly();

        $filteredSuppliers = Supplier::scoped(new ByOrdersAndSupplierUsersWithoutOrdersInUser($user))->get();
        $this->assertCount(3, $filteredSuppliers);

        $supplierOccurrences = $filteredSuppliers->countBy(function($supplier) {
            return $supplier->getKey();
        });

        foreach ($supplierOccurrences as $occurrence) {
            $this->assertEquals(1, $occurrence);
        }
    }

    /** @test */
    public function it_returns_only_the_common_suppliers_who_have_pubnub_channels_with_the_user()
    {
        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->published()->count(2)->createQuietly();
        SupplierUser::factory()->usingUser($user)->count(2)->createQuietly();

        $suppliers->each(function($supplier, $index) use ($user) {
            SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();
            PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier)->create([
                'created_at' => Carbon::now()
                    ->subMinutes($index),
            ]);
        });

        $expectedSupplierKeys = $suppliers->pluck(Supplier::keyName())->toArray();

        $filteredSuppliers    = Supplier::scoped(new ByOrdersAndSupplierUsersWithoutOrdersInUser($user))->get();
        $filteredSupplierKeys = $filteredSuppliers->pluck(Supplier::keyName())->toArray();

        $this->assertEquals($expectedSupplierKeys, $filteredSupplierKeys);
    }

    /** @test */
    public function it_return_the_supplier_once_in_the_suppliers_with_orders_group_if_it_has_a_order_with_the_user_and_is_a_common_supplier_with_pubnub_channel(
    )
    {
        $user      = User::factory()->create();
        $orders    = Order::factory()->usingUser($user)->count(2)->sequence(function(Sequence $sequence) {
            return ['updated_at' => Carbon::now()->subSeconds($sequence->index)];
        })->createQuietly();
        $suppliers = Supplier::factory()->published()->count(2)->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($orders->first()->supplier)->create();

        $suppliers->each(function(Supplier $supplier, int $index) use ($user) {
            SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();
            PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier)->create([
                'created_at' => Carbon::now()
                    ->subMinutes($index),
            ]);
        });

        $expectedSupplierKeys = [
            ...$orders->pluck('supplier.' . Supplier::keyName()),
            ...$suppliers->pluck(Supplier::keyName()),
        ];

        $filteredSuppliers    = Supplier::scoped(new ByOrdersAndSupplierUsersWithoutOrdersInUser($user))->get();
        $filteredSupplierKeys = $filteredSuppliers->pluck(Supplier::keyName())->toArray();

        $this->assertEquals($expectedSupplierKeys, $filteredSupplierKeys);
    }
}
