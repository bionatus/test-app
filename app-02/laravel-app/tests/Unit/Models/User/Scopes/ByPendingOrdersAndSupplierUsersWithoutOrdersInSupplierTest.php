<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\PubnubChannel;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use App\Models\User\Scopes\ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplierTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_the_users_with_pending_or_pending_approval_orders_with_the_supplier_and_the_common_supplier_users(
    )
    {
        $supplier              = Supplier::factory()->createQuietly();
        $pendingOrders         = Order::factory()->usingSupplier($supplier)->pending()->count(3)->create();
        $pendingApprovalOrders = Order::factory()->usingSupplier($supplier)->pendingApproval()->count(2)->create();
        $supplierUsers         = SupplierUser::factory()->usingSupplier($supplier)->count(2)->create();
        Order::factory()->usingSupplier($supplier)->approved()->count(5)->create();
        Order::factory()->usingSupplier($supplier)->completed()->count(5)->create();
        Order::factory()->usingSupplier($supplier)->canceled()->count(5)->create();
        Order::factory()->pending()->count(3)->createQuietly();
        Order::factory()->pendingApproval()->count(2)->createQuietly();
        SupplierUser::factory()->count(2)->createQuietly();

        $expectedUserKeys = [
            ...$pendingOrders->pluck('user.' . User::keyName()),
            ...$pendingApprovalOrders->pluck('user.' . User::keyName()),
            ...$supplierUsers->pluck('user_' . User::keyName()),
        ];

        $filteredUsers    = User::scoped(new ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier($supplier))->get();
        $filteredUserKeys = $filteredUsers->pluck(User::keyName())->toArray();

        $this->assertEqualsCanonicalizing($expectedUserKeys, $filteredUserKeys);
    }

    /** @test */
    public function it_returns_the_users_with_pending_or_pending_approval_orders_with_the_supplier_and_the_common_supplier_users_with_pubnub_channel_ordered_by_pubnub_channel_last_message_at(
    )
    {
        $now           = Carbon::now();
        $supplier      = Supplier::factory()->createQuietly();
        $order1        = Order::factory()->usingSupplier($supplier)->pending()->create();
        $order2        = Order::factory()->usingSupplier($supplier)->pending()->create();
        $order3        = Order::factory()->usingSupplier($supplier)->pending()->create();
        $supplierUser1 = SupplierUser::factory()->usingSupplier($supplier)->create();
        $supplierUser2 = SupplierUser::factory()->usingSupplier($supplier)->create();
        $supplierUser3 = SupplierUser::factory()->usingSupplier($supplier)->create();
        Order::factory()->usingSupplier($supplier)->approved()->count(5)->create();
        Order::factory()->usingSupplier($supplier)->completed()->count(5)->create();
        Order::factory()->usingSupplier($supplier)->canceled()->count(5)->create();
        Order::factory()->pending()->count(3)->createQuietly();
        Order::factory()->pendingApproval()->count(2)->createQuietly();
        SupplierUser::factory()->count(2)->createQuietly();
        PubnubChannel::factory()->usingSupplier($order1->supplier)->usingUser($order1->user)->create([
            'supplier_last_message_at' => null,
            'user_last_message_at'     => null,
            'updated_at'               => $now->clone()->subSeconds(60),
        ]);
        PubnubChannel::factory()->usingSupplier($order2->supplier)->usingUser($order2->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(40),
            'user_last_message_at'     => null,
            'updated_at'               => $now->clone()->subSeconds(40),
        ]);
        PubnubChannel::factory()->usingSupplier($order3->supplier)->usingUser($order3->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(20),
            'user_last_message_at'     => $now->clone()->subSeconds(25),
            'updated_at'               => $now->clone()->subSeconds(20),
        ]);
        PubnubChannel::factory()->usingSupplier($supplierUser1->supplier)->usingUser($supplierUser1->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(55),
            'user_last_message_at'     => $now->clone()->subSeconds(50),
            'updated_at'               => $now->clone()->subSeconds(50),
        ]);
        PubnubChannel::factory()->usingSupplier($supplierUser2->supplier)->usingUser($supplierUser2->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(30),
            'user_last_message_at'     => $now->clone()->subSeconds(31),
            'updated_at'               => $now->clone()->subSeconds(30),
        ]);
        PubnubChannel::factory()->usingSupplier($supplierUser3->supplier)->usingUser($supplierUser3->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(15),
            'user_last_message_at'     => $now->clone()->subSeconds(10),
            'updated_at'               => $now->clone()->subSeconds(10),
        ]);

        $expectedUserKeys = [
            $supplierUser3->user->getKey(),
            $order3->user->getKey(),
            $supplierUser2->user->getKey(),
            $order2->user->getKey(),
            $supplierUser1->user->getKey(),
            $order1->user->getKey(),
        ];

        $filteredUsers    = User::scoped(new ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier($supplier))->get();
        $filteredUserKeys = $filteredUsers->pluck(User::keyName())->toArray();

        $this->assertEquals($expectedUserKeys, $filteredUserKeys);
    }

    /** @test */
    public function it_return_the_user_just_once_if_this_has_multiple_pending_or_pending_approval_orders_with_the_supplier(
    )
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->pending()->count(3)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->pendingApproval()->count(3)->create();
        Order::factory()->usingSupplier($supplier)->pending()->count(2)->create();
        User::factory()->count(5)->create();

        $filteredUsers = User::scoped(new ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier($supplier))->get();

        $this->assertCount(3, $filteredUsers);

        $userOccurrences = $filteredUsers->countBy(function($user) {
            return $user->getKey();
        });

        foreach ($userOccurrences as $occurrence) {
            $this->assertEquals(1, $occurrence);
        }
    }

    /** @test */
    public function it_return_the_user_just_once_if_this_has_a_pending_or_pending_approval_orders_with_the_supplier_and_is_a_common_supplier_user(
    )
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->pending()->count(3)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->pendingApproval()->count(3)->create();
        Order::factory()->usingSupplier($supplier)->pending()->count(2)->create();
        SupplierUser::factory()->usingSupplier($supplier)->usingUser($user)->create();
        SupplierUser::factory()->usingSupplier($supplier)->count(2)->create();
        User::factory()->count(5)->create();

        $filteredUsers = User::scoped(new ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier($supplier))->get();

        $this->assertCount(5, $filteredUsers);

        $userOccurrences = $filteredUsers->countBy(function($user) {
            return $user->getKey();
        });

        foreach ($userOccurrences as $occurrence) {
            $this->assertEquals(1, $occurrence);
        }
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_shows_if_the_user_has_orders_in_status_pending_or_pending_approval_with_working_on_it(
        int $substatusId,
        ?string $workingOnIt,
        bool $expectedResult
    ) {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingSupplier($supplier)->create();
        $order = Order::factory()->usingSupplier($supplier)->usingUser($user)->create([
            'working_on_it' => $workingOnIt,
        ]);
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $filteredUser = User::scoped(new ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier($supplier))
            ->get()
            ->first();

        $this->assertEquals($expectedResult, $filteredUser->orders_exists);
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, null, false],
            [Substatus::STATUS_PENDING_REQUESTED, 'John Doe', true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, null, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, 'John Doe', true],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, null, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, 'John Doe', false],
            [Substatus::STATUS_COMPLETED_DONE, null, false],
            [Substatus::STATUS_COMPLETED_DONE, 'John Doe', false],
            [Substatus::STATUS_CANCELED_REJECTED, null, false],
            [Substatus::STATUS_CANCELED_REJECTED, 'John Doe', false],
        ];
    }

    /** @test */
    public function it_shows_how_many_orders_in_status_pending_or_pending_approval_has_the_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingSupplier($supplier)->usingUser($user)->create();
        Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->pending()
            ->count($pendingOrdersCount = 2)
            ->create();
        Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->pendingApproval()
            ->count($pendingApprovalOrdersCount = 3)
            ->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->approved()->count(2)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->completed()->count(2)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($user)->canceled()->count(1)->create();
        PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $filteredUser = User::scoped(new ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier($supplier))
            ->get()
            ->first();

        $this->assertEquals($pendingOrdersCount, $filteredUser->pending_orders_count);
        $this->assertEquals($pendingApprovalOrdersCount, $filteredUser->pending_approval_orders_count);
    }

    /** @test */
    public function it_shows_orders_whose_user_name_or_company_name_match_search_string()
    {
        $searchString  = 'search user';
        $userFirstName = User::factory()->create(['first_name' => 'search user first name']);
        $userLastName  = User::factory()->create(['last_name' => 'search user last name']);
        $userNoSearch  = User::factory()->create();
        $company       = Company::factory()->create(['name' => 'search user company name']);
        $userCompany   = User::factory()->create();
        CompanyUser::factory()->usingCompany($company)->usingUser($userCompany)->create();

        $supplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingSupplier($supplier)->usingUser($userFirstName)->create();
        SupplierUser::factory()->usingSupplier($supplier)->usingUser($userLastName)->create();
        SupplierUser::factory()->usingSupplier($supplier)->usingUser($userCompany)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($userFirstName)->pending()->count(2)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($userLastName)->pendingApproval()->count(3)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($userNoSearch)->pendingApproval()->count(7)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($userNoSearch)->completed()->count(9)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($userNoSearch)->canceled()->count(1)->create();

        $filteredUser = User::scoped(new ByPendingOrdersAndSupplierUsersWithoutOrdersInSupplier($supplier,
            $searchString))->get();

        $this->assertEquals(3, $filteredUser->count());
    }
}
