<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\Device;
use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\PubnubChannel;
use App\Models\Scopes\BySupplier;
use App\Models\Scopes\NewestUpdated;
use App\Models\Scopes\Oldest;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use App\Models\User\Scopes\WithSupplierRelationships;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithSupplierRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_the_corresponding_relationships_for_the_user()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();
        PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create();
        SupplierUser::factory()->usingSupplier($supplier)->usingUser($user)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->pending()->count(3)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->pendingApproval()->count(4)->create();
        Device::factory()->usingUser($user)->create();
        $expectedRelations = [
            'pubnubChannels' => $user->pubnubChannels()->scoped(new BySupplier($supplier))->get(),
            'supplierUsers'  => $user->supplierUsers()->scoped(new BySupplier($supplier))->get(),
            'orders'         => $user->orders()
                ->scoped(new BySupplier($supplier))
                ->scoped(new ByLastSubstatuses(Substatus::STATUSES_PENDING))
                ->scoped(new Oldest())
                ->get(),
            'devices'        => $user->devices()->scoped(new NewestUpdated())->get(),
        ];

        $emptyUser = User::find($user->getKey());
        $this->assertEmpty($emptyUser->getRelations());

        $filledUser = User::scoped(new WithSupplierRelationships($supplier))->find($user->getKey());
        $this->assertNotEmpty($filledUser->getRelations());
        $this->assertEquals($expectedRelations, $filledUser->getRelations());
    }

    /** @test */
    public function it_adds_pending_orders_count_property_to_the_user()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();

        $emptyUser = User::find($user->getKey());
        $this->assertFalse($emptyUser->__isset('pending_orders_count'));

        $filledUser = User::scoped(new WithSupplierRelationships($supplier))->find($user->getKey());
        $this->assertTrue($filledUser->__isset('pending_orders_count'));
    }

    /** @test */
    public function it_adds_pending_approval_orders_count_property_to_the_user()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();

        $emptyUser = User::find($user->getKey());
        $this->assertFalse($emptyUser->__isset('pending_approval_orders_count'));

        $filledUser = User::scoped(new WithSupplierRelationships($supplier))->find($user->getKey());
        $this->assertTrue($filledUser->__isset('pending_approval_orders_count'));
    }

    /** @test */
    public function it_adds_orders_exists_property_to_the_user()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();

        $emptyUser = User::find($user->getKey());
        $this->assertFalse($emptyUser->__isset('orders_exists'));

        $filledUser = User::scoped(new WithSupplierRelationships($supplier))->find($user->getKey());
        $this->assertTrue($filledUser->__isset('orders_exists'));
    }

    /** @test */
    public function it_returns_the_count_of_pending_and_pending_approval_orders_for_the_user()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();
        Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->pending()
            ->count($pendingOrdersCount = 3)
            ->create();
        Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->pendingApproval()
            ->count($pendingApprovalOrdersCount = 4)
            ->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->approved()->count(2)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->completed()->count(2)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->canceled()->count(2)->create();
        Order::factory()->pending()->count(3)->createQuietly();
        Order::factory()->pendingApproval()->count(2)->createQuietly();

        $filteredUser = User::scoped(new WithSupplierRelationships($supplier))->first();

        $this->assertEquals($pendingOrdersCount, $filteredUser->pending_orders_count);
        $this->assertEquals($pendingApprovalOrdersCount, $filteredUser->pending_approval_orders_count);
    }

    /** @test */
    public function it_returns_if_exists_pending_and_pending_approval_orders_with_working_on_it_for_the_user()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->pending()->count(5)->create([
            'working_on_it' => 'someone',
        ]);
        Order::factory()->usingSupplier($supplier)->usingUser($user)->pendingApproval()->count(7)->create([
            'working_on_it' => 'someone',
        ]);

        Order::factory()->usingSupplier($supplier)->usingUser($user)->pending()->count(1)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->pendingApproval()->count(2)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->approved()->count(2)->create([
            'working_on_it' => 'someone',
        ]);
        Order::factory()->usingSupplier($supplier)->usingUser($user)->completed()->count(2)->create([
            'working_on_it' => 'someone',
        ]);
        Order::factory()->usingSupplier($supplier)->usingUser($user)->canceled()->count(2)->create([
            'working_on_it' => 'someone',
        ]);
        Order::factory()->pending()->count(2)->createQuietly();
        Order::factory()->pendingApproval()->count(2)->createQuietly();

        $filteredUser = User::scoped(new WithSupplierRelationships($supplier))->first();

        $this->assertEquals(12, $filteredUser->orders_exists);
    }
}
