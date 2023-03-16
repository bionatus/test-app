<?php

namespace Tests\Feature\LiveApi\V1\Supplier\User;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Supplier\UserController;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\PubnubChannel;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see UserController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_SUPPLIER_USER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_users_that_has_pending_or_pending_approval_orders_with_supplier_ordered_by_last_message_at(
    )
    {
        $now           = Carbon::now();
        $supplier      = Supplier::factory()->withEmail()->createQuietly();
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
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
        PubnubChannel::factory()->usingSupplier($supplier)->usingUser($order1->user)->create([
            'supplier_last_message_at' => null,
            'user_last_message_at'     => null,
            'updated_at'               => $now->clone()->subSeconds(60),
        ]);
        PubnubChannel::factory()->usingSupplier($supplier)->usingUser($order2->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(40),
            'user_last_message_at'     => null,
            'updated_at'               => $now->clone()->subSeconds(40),
        ]);
        PubnubChannel::factory()->usingSupplier($supplier)->usingUser($order3->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(20),
            'user_last_message_at'     => $now->clone()->subSeconds(25),
            'updated_at'               => $now->clone()->subSeconds(20),
        ]);
        PubnubChannel::factory()->usingSupplier($supplier)->usingUser($supplierUser1->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(55),
            'user_last_message_at'     => $now->clone()->subSeconds(50),
            'updated_at'               => $now->clone()->subSeconds(50),
        ]);
        PubnubChannel::factory()->usingSupplier($supplier)->usingUser($supplierUser2->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(30),
            'user_last_message_at'     => $now->clone()->subSeconds(31),
            'updated_at'               => $now->clone()->subSeconds(30),
        ]);
        PubnubChannel::factory()->usingSupplier($supplier)->usingUser($supplierUser3->user)->create([
            'supplier_last_message_at' => $now->clone()->subSeconds(15),
            'user_last_message_at'     => $now->clone()->subSeconds(10),
            'updated_at'               => $now->clone()->subSeconds(10),
        ]);
        $supplier2 = Supplier::factory()->withEmail()->createQuietly();
        PubnubChannel::factory()->usingSupplier($supplier2)->usingUser($order1->user)->create();

        $expectedUserKeys = [
            $supplierUser3->user->getKey(),
            $order3->user->getKey(),
            $supplierUser2->user->getKey(),
            $order2->user->getKey(),
            $supplierUser1->user->getKey(),
            $order1->user->getKey(),
        ];

        Auth::shouldUse('live');

        $this->login($staff);
        $response = $this->get(URL::route($this->routeName));
        $response->assertStatus(Response::HTTP_OK);
        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawUser, int $index) use ($expectedUserKeys) {
            $expectedUserKey = $expectedUserKeys[$index];
            $this->assertSame($expectedUserKey, $rawUser['id']);
        });
    }

    /** @test */
    public function it_displays_a_list_of_users_searching_by_first_name_or_last_name_or_company_name()
    {
        $userFirstName   = User::factory()->create(['first_name' => 'search_name']);
        $userLastName    = User::factory()->create(['last_name' => 'search_last_name']);
        $userCompanyName = User::factory()->create();
        $company         = Company::factory()->create(['name' => 'search_company_name']);
        CompanyUser::factory()->usingUser($userCompanyName)->usingCompany($company);
        $otherUser = User::factory()->create();
        $supplier  = Supplier::factory()->withEmail()->createQuietly();
        $staff     = Staff::factory()->usingSupplier($supplier)->create();
        Order::factory()->usingSupplier($supplier)->usingUser($userFirstName)->pending()->create();
        Order::factory()->usingSupplier($supplier)->usingUser($userLastName)->pending()->create();
        Order::factory()->usingSupplier($supplier)->usingUser($otherUser)->pending()->create();
        SupplierUser::factory()->usingSupplier($supplier)->usingUser($userCompanyName)->create();

        Order::factory()->usingSupplier($supplier)->approved()->count(5)->create();
        Order::factory()->usingSupplier($supplier)->completed()->count(5)->create();
        Order::factory()->usingSupplier($supplier)->canceled()->count(5)->create();
        Order::factory()->pending()->count(3)->createQuietly();
        Order::factory()->pendingApproval()->count(2)->createQuietly();

        $expectedUserKeys = [
            $userFirstName->getKey(),
            $userLastName->getKey(),
            $userCompanyName->getKey(),
        ];

        Auth::shouldUse('live');

        $this->login($staff);
        $response = $this->get(URL::route($this->routeName, [RequestKeys::SEARCH_STRING => 'search']));
        $response->assertStatus(Response::HTTP_OK);
        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawUser) use ($expectedUserKeys) {
            $this->assertTrue(in_array($rawUser['id'], $expectedUserKeys));
        });
    }

    /** @test */
    public function it_applies_sorting_correctly_on_each_page()
    {
        $date1 = Carbon::now()->subMinutes(10)->toIso8601String();
        $date2 = Carbon::now()->subMinutes(20)->toIso8601String();

        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        $pubnubChannelsWithUserMessages = PubnubChannel::factory()->usingSupplier($supplier)->count(20)->sequence(fn(
            Sequence $sequence
        ) => [
            'user_last_message_at' => $sequence->index % 2 === 0 ? $date1 : $date2,
        ])->create(['supplier_last_message_at' => null]);

        SupplierUser::factory()->createManyQuietly($pubnubChannelsWithUserMessages->map(function(
            PubnubChannel $pubnubChannel
        ) {
            return ['supplier_id' => $pubnubChannel->supplier_id, 'user_id' => $pubnubChannel->user_id];
        }));

        $pubnubChannelsWithSupplierMessages = PubnubChannel::factory()
            ->usingSupplier($supplier)
            ->count(20)
            ->sequence(fn(Sequence $sequence) => [
                'supplier_last_message_at' => $sequence->index % 2 === 0 ? $date1 : $date2,
            ])
            ->create(['user_last_message_at' => null]);

        SupplierUser::factory()->createManyQuietly($pubnubChannelsWithSupplierMessages->map(function(
            PubnubChannel $pubnubChannel
        ) {
            return ['supplier_id' => $pubnubChannel->supplier_id, 'user_id' => $pubnubChannel->user_id];
        }));

        $sortedPubnubChannels = $pubnubChannelsWithUserMessages->merge($pubnubChannelsWithSupplierMessages)->sortBy([
            fn(
                PubnubChannel $pubnubChannelA,
                PubnubChannel $pubnubChannelB
            ) => max($pubnubChannelA->user_last_message_at,
                    $pubnubChannelA->supplier_last_message_at) <=> max($pubnubChannelB->user_last_message_at,
                    $pubnubChannelB->supplier_last_message_at),
            fn(
                PubnubChannel $pubnubChannelA,
                PubnubChannel $pubnubChannelB
            ) => $pubnubChannelA->updated_at <=> $pubnubChannelB->updated_at,
            fn(
                PubnubChannel $pubnubChannelA,
                PubnubChannel $pubnubChannelB
            ) => $pubnubChannelA->user->id <=> $pubnubChannelB->user->id,
        ])->reverse();

        Auth::shouldUse('live');

        $this->login($staff);

        $expectedUserKeys = $sortedPubnubChannels->chunk(15, function() {
        });

        $alreadyReturned = Collection::make();
        $expectedUserKeys->each(function(Collection $expectedKeysForPage, int $pageIndex) use ($alreadyReturned) {

            $response = $this->get(URL::route($this->routeName, [RequestKeys::PAGE => $page = $pageIndex + 1]));
            $response->assertStatus(Response::HTTP_OK);
            $data = Collection::make($response->json('data'));

            $data->each(function(array $rawUser) use ($page, $alreadyReturned, $expectedKeysForPage) {
                $this->assertNotContains($rawUser['id'], $alreadyReturned, "Repeated record on page $page");
            });
            $alreadyReturned->push(...$data->pluck('id'));

            $this->assertEquals($expectedKeysForPage->pluck('user.id'), $data->pluck('id'));
        });
    }
}
