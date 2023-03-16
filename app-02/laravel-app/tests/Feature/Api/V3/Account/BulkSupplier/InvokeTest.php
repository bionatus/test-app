<?php

namespace Tests\Feature\Api\V3\Account\BulkSupplier;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Events\PubnubChannel\Created;
use App\Events\Supplier\Selected;
use App\Events\Supplier\Unselected;
use App\Events\User\SuppliersUpdated;
use App\Http\Controllers\Api\V3\Account\BulkSupplierController;
use App\Http\Requests\Api\V3\Account\BulkSupplier\InvokeRequest;
use App\Http\Resources\Api\V3\Account\BulkSupplier\BaseResource;
use App\Jobs\LogActivity;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\Phone;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Bus;
use Carbon\Carbon;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see BulkSupplierController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_BULK_SUPPLIER_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_syncs_user_suppliers()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);
        Bus::fake();

        $user                 = User::factory()->create();
        $oldSupplierWithOrder = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($oldSupplierWithOrder)->usingUser($user)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($oldSupplierWithOrder)->create();

        $supplierWithFilledFields = SupplierUser::factory()
            ->usingUser($user)
            ->createQuietly(['customer_tier' => 'foo']);

        $notVisibleSupplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($notVisibleSupplier)
            ->create(['visible_by_user' => false]);

        $notVisibleSuppliers = Collection::make([
            $notVisibleSupplier,
            $oldSupplierWithOrder,
            $supplierWithFilledFields->supplier,
        ]);

        $willBeVisibleSupplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($willBeVisibleSupplier)
            ->create(['visible_by_user' => false]);

        $oldSupplierUsers = SupplierUser::factory()->usingUser($user)->count(2)->createQuietly();
        $currentSuppliers = Supplier::factory()->published()->count(3)->createQuietly();
        $currentSuppliers->push($willBeVisibleSupplier);

        Supplier::factory()->published()->count(4)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $currentSuppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data = Collection::make($response->json('data'));

        $oldSupplierUsers->each(function(SupplierUser $storeUser) {
            $this->assertDeleted($storeUser);
        });

        $this->assertDatabaseCount(SupplierUser::tableName(), $currentSuppliers->count() + 3);

        $currentSuppliers->each(function(Supplier $supplier) use ($user) {
            $this->assertDatabaseHas(SupplierUser::tableName(), [
                'supplier_id'     => $supplier->getKey(),
                'user_id'         => $user->getKey(),
                'visible_by_user' => true,
            ]);
        });

        $notVisibleSuppliers->each(function(Supplier $supplier) use ($user) {
            $this->assertDatabaseHas(SupplierUser::tableName(), [
                'supplier_id'     => $supplier->getKey(),
                'user_id'         => $user->getKey(),
                'visible_by_user' => false,
            ]);
        });

        $this->assertEqualsCanonicalizing($currentSuppliers->pluck(Supplier::routeKeyName()), $data->pluck('id'));
    }

    /** @test */
    public function it_attached_a_supplier()
    {
        Bus::fake();
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user             = User::factory()->create();
        $attachedSupplier = Supplier::factory()->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [$attachedSupplier->getRouteKey()],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'user_id'         => $user->getKey(),
            'supplier_id'     => $attachedSupplier->getKey(),
            'visible_by_user' => true,
        ]);
    }

    /** @test */
    public function it_detached_a_supplier()
    {
        Bus::fake();
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user             = User::factory()->create();
        $detachedSupplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($detachedSupplier)->create();

        $route = URL::route($this->routeName);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [Supplier::factory()->createQuietly()->getRouteKey()],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseMissing(SupplierUser::tableName(), [
            'user_id'     => $user->getKey(),
            'supplier_id' => $detachedSupplier->getKey(),
        ]);
    }

    /** @test */
    public function it_sets_visible_by_user_in_false_when_detached_supplier_has_an_order_with_the_user()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user              = User::factory()->create();
        $supplierWithOrder = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($supplierWithOrder)->usingUser($user)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplierWithOrder)->create();

        $route = URL::route($this->routeName);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [Supplier::factory()->createQuietly()->getRouteKey()],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'supplier_id'     => $supplierWithOrder->getKey(),
            'user_id'         => $user->getKey(),
            'visible_by_user' => false,
        ]);
    }

    /** @test */
    public function it_sets_visible_by_user_in_false_when_detached_supplier_has_info_in_relationship_fields()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user                 = User::factory()->create();
        $supplierCustomerTier = Supplier::factory()->createQuietly();
        $supplierCashBuyer    = Supplier::factory()->createQuietly();

        SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($supplierCustomerTier)
            ->create(['customer_tier' => 'foo']);
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplierCashBuyer)->create(['cash_buyer' => 1]);

        $route = URL::route($this->routeName);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [Supplier::factory()->createQuietly()->getRouteKey()],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'supplier_id'     => $supplierCustomerTier->getKey(),
            'user_id'         => $user->getKey(),
            'visible_by_user' => false,
        ]);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'supplier_id'     => $supplierCashBuyer->getKey(),
            'user_id'         => $user->getKey(),
            'visible_by_user' => false,
        ]);
    }

    /** @test */
    public function it_does_not_happen_anything_when_supplier_is_not_visible()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user               = User::factory()->create();
        $notVisibleSupplier = Supplier::factory()->createQuietly();

        SupplierUser::factory()->usingUser($user)->usingSupplier($notVisibleSupplier)->notVisible()->create();

        $route = URL::route($this->routeName);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [Supplier::factory()->createQuietly()->getRouteKey()],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'supplier_id'     => $notVisibleSupplier->getKey(),
            'user_id'         => $user->getKey(),
            'visible_by_user' => false,
        ]);
    }

    /** @test */
    public function it_will_be_visible_a_not_visible_supplier()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user               = User::factory()->create();
        $notVisibleSupplier = Supplier::factory()->createQuietly();

        SupplierUser::factory()->usingUser($user)->usingSupplier($notVisibleSupplier)->notVisible()->create();

        $route = URL::route($this->routeName);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [$notVisibleSupplier->getRouteKey()],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'supplier_id'     => $notVisibleSupplier->getKey(),
            'user_id'         => $user->getKey(),
            'visible_by_user' => true,
        ]);
    }

    /** @test */
    public function it_set_preferred_supplier()
    {
        Bus::fake();
        Event::fake([SuppliersUpdated::class, Selected::class]);

        $user              = User::factory()->create();
        $currentSuppliers  = Supplier::factory()->published()->count(3)->createQuietly();
        $preferredSupplier = Supplier::factory()->published()->createQuietly();
        $totalSuppliers    = $currentSuppliers->push($preferredSupplier);
        Supplier::factory()->count(4)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $totalSuppliers->pluck(Supplier::routeKeyName())->toArray(),
            RequestKeys::PREFERRED => $preferredSupplier->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data = Collection::make($response->json('data'));

        $this->assertDatabaseCount(SupplierUser::tableName(), $currentSuppliers->count());

        $user->suppliers->each(function(Supplier $supplier) use ($preferredSupplier) {
            $this->assertDatabaseHas(SupplierUser::tableName(), [
                'supplier_id' => $supplier->getKey(),
                'preferred'   => $preferredSupplier->getKey() === $supplier->getKey() ? true : null,
            ]);
        });

        $this->assertEqualsCanonicalizing($currentSuppliers->pluck(Supplier::routeKeyName()), $data->pluck('id'));
    }

    /** @test */
    public function it_dispatches_a_supplier_updated_event()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user = User::factory()->create();
        SupplierUser::factory()->usingUser($user)->count(2)->createQuietly();
        $currentSuppliers = Supplier::factory()->published()->count(3)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $currentSuppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(SuppliersUpdated::class, function(SuppliersUpdated $event) use ($user) {
            $this->assertSame($user->getKey(), $event->user()->getKey());

            return true;
        });
    }

    /** @test */
    public function it_dispatches_a_selected_events_for_every_supplier()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user = User::factory()->create();
        SupplierUser::factory()->usingUser($user)->count(2)->createQuietly();
        $currentSuppliers = Supplier::factory()->count(3)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $currentSuppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatchedTimes(Selected::class, $currentSuppliers->count());
    }

    /** @test */
    public function it_does_not_dispatch_selected_events_when_not_visible_supplier_will_be_visible()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user               = User::factory()->create();
        $notVisibleSupplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($notVisibleSupplier)
            ->create(['visible_by_user' => false]);

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [$notVisibleSupplier->pluck(Supplier::routeKeyName())],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertNotDispatched(Selected::class);
    }

    /** @test */
    public function it_dispatches_an_unselected_events_for_every_supplier()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user = User::factory()->create();
        SupplierUser::factory()->usingUser($user)->count($unselectedSuppliersCount = 2)->createQuietly();
        $newSuppliers = Supplier::factory()->count(3)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $newSuppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatchedTimes(Unselected::class, $unselectedSuppliersCount);
    }

    /** @test */
    public function it_does_not_dispatch_unselected_events_when_suppliers_have_orders_or_filled_fields()
    {
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user                     = User::factory()->create();
        $supplierWithOrder        = Supplier::factory()->createQuietly();
        $supplierWithFilledFields = Supplier::factory()->createQuietly();
        Order::factory()->usingUser($user)->usingSupplier($supplierWithOrder)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplierWithOrder)->create();
        SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($supplierWithFilledFields)
            ->create(['customer_tier' => 'foo', 'cash_buyer' => true]);

        $newSuppliers = Supplier::factory()->count(3)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $newSuppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertNotDispatched(Unselected::class);
    }

    /** @test */
    public function it_does_nothing_when_nothing_changes()
    {
        Bus::fake();
        Event::fake([SuppliersUpdated::class, Selected::class]);

        $user             = User::factory()->create();
        $currentSuppliers = SupplierUser::factory()->usingUser($user)->count(3)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $currentSuppliers->pluck('supplier.' . Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertNotDispatched(SuppliersUpdated::class);
        Event::assertNotDispatched(Selected::class);
        Bus::assertNotDispatched(LogActivity::class);
    }

    /** @test */
    public function it_creates_a_new_pubnub_channel_per_every_supplier_added_when_it_does_not_exits()
    {
        Event::fake([SuppliersUpdated::class, Selected::class]);

        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->published()->count(3)->createQuietly();
        Supplier::factory()->count(4)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $suppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(PubnubChannel::tableName(), $suppliers->count());

        $suppliers->each(function(Supplier $supplier) use ($user) {
            $this->assertDatabaseHas(PubnubChannel::tableName(), [
                'supplier_id' => $supplier->getKey(),
                'user_id'     => $user->getKey(),
            ]);
        });
    }

    /** @test */
    public function it_does_not_create_a_new_pubnub_channel_when_the_supplier_is_not_published()
    {
        Event::fake([SuppliersUpdated::class, Selected::class]);

        $user                = User::factory()->create();
        $publishedSuppliers  = Supplier::factory()->published()->count(3)->createQuietly();
        $unpublishedSupplier = Supplier::factory()->createQuietly();
        Supplier::factory()->count(4)->createQuietly();
        $suppliers = Collection::make([
            ...$publishedSuppliers,
            $unpublishedSupplier,
        ]);

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $suppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(PubnubChannel::tableName(), $publishedSuppliers->count());
        $this->assertDatabaseMissing(PubnubChannel::tableName(), [
            'supplier_id' => $unpublishedSupplier->getKey(),
            'user_id'     => $user->getKey(),
        ]);

        $publishedSuppliers->each(function(Supplier $supplier) use ($user) {
            $this->assertDatabaseHas(PubnubChannel::tableName(), [
                'supplier_id' => $supplier->getKey(),
                'user_id'     => $user->getKey(),
            ]);
        });
    }

    /** @test */
    public function it_does_not_create_a_new_pubnub_channel_when_it_exits_previously()
    {
        Event::fake([SuppliersUpdated::class, Selected::class]);

        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->published()->count(3)->createQuietly();
        PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier = $suppliers->first())->create([
            'created_at' => $createdAt = Carbon::now()->subDay(),
        ]);
        Supplier::factory()->published()->count(4)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $suppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(PubnubChannel::tableName(), $suppliers->count());
        $this->assertDatabaseHas(PubnubChannel::tableName(), [
            'supplier_id' => $supplier->getKey(),
            'user_id'     => $user->getKey(),
            'created_at'  => $createdAt,
        ]);
    }

    /** @test */
    public function it_verifies_user_if_all_conditions_are_met()
    {
        Event::fake(Selected::class);

        $user = User::factory()->create([
            'first_name' => 'Jon',
            'last_name'  => 'Doe',
            'zip'        => 12345,
            'address'    => '754 Evergreen Av',
            'country'    => 'US',
            'state'      => 'Unknown',
            'city'       => 'Springfield',
        ]);
        Phone::factory()->usingUser($user)->create();
        CompanyUser::factory()->usingUser($user)->create();
        $currentSuppliers = Supplier::factory()->published()->count(3)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);

        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $currentSuppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertTrue($user->fresh()->isVerified());
    }

    /** @test */
    public function it_sends_an_automatic_message_when_pubnub_channel_is_created()
    {
        Event::fake([Created::class, SuppliersUpdated::class, Selected::class]);
        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->published()->count(3)->createQuietly();
        Supplier::factory()->count(4)->createQuietly();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $suppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $suppliers->each(function(Supplier $supplier) use ($user) {
            Event::assertDispatched(Created::class, function(Created $event) use ($user, $supplier) {
                $userId            = $user->getKey();
                $supplierId        = $supplier->getKey();
                $channelUserId     = $event->pubnubChannel()->user->getKey();
                $channelSupplierId = $event->pubnubChannel()->supplier->getKey();

                return $channelUserId == $userId && $channelSupplierId == $supplierId;
            });
        });
    }

    /** @test */
    public function it_not_sends_an_automatic_message_when_pubnub_channel_exist()
    {
        Event::fake([Created::class, SuppliersUpdated::class, Selected::class]);
        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->published()->count(2)->createQuietly();
        $suppliers->each(function(Supplier $supplier) use ($user) {
            PubnubChannel::factory()->usingUser($user)->usingSupplier($supplier)->create();
        });

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => $suppliers->pluck(Supplier::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        Event::assertNotDispatched(Created::class);
    }

    /** @test */
    public function it_dispatch_a_activity_job_when_attached_a_supplier()
    {
        Bus::fake();
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user              = User::factory()->create();
        $attachedSupplier1 = Supplier::factory()->createQuietly();
        $attachedSupplier2 = Supplier::factory()->createQuietly();
        $attachedSupplier3 = Supplier::factory()->createQuietly();

        SupplierUser::factory()->usingUser($user)->usingSupplier($attachedSupplier1)->create();

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [
                $attachedSupplier1->getRouteKey(),
                $attachedSupplier2->getRouteKey(),
                $attachedSupplier3->getRouteKey(),
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Bus::assertDispatchedTimes(LogActivity::class, 2);
    }

    /** @test */
    public function it_dispatch_a_activity_job_when_detached_a_supplier()
    {
        Bus::fake();
        Event::fake([SuppliersUpdated::class, Selected::class, Unselected::class]);

        $user              = User::factory()->create();
        $detachedSupplier1 = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($detachedSupplier1)->create();
        $detachedSupplier2 = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($detachedSupplier2)->create();
        $supplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();

        $route = URL::route($this->routeName);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [$supplier->getRouteKey()],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Bus::assertDispatchedTimes(LogActivity::class, 2);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_dispatch_a_activity_job_when_set_preferred_supplier($preferred, $dispatchedTimes)
    {
        Bus::fake();
        Event::fake([SuppliersUpdated::class, Selected::class]);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->published()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $preferredSupplier = Supplier::factory()->published()->createQuietly();
        SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($preferredSupplier)
            ->create(['preferred' => $preferred]);

        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::SUPPLIERS => [$supplier->getRouteKey(), $preferredSupplier->getRouteKey()],
            RequestKeys::PREFERRED => $preferredSupplier->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Bus::assertDispatchedTimes(LogActivity::class, $dispatchedTimes);
    }

    public function dataProvider(): array
    {
        return [
            [true, 0],
            [false, 1],
        ];
    }
}
