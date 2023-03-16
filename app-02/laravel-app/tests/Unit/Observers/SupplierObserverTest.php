<?php

namespace Tests\Unit\Observers;

use App;
use App\Events\PubnubChannel\Created;
use App\Models\PubnubChannel;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Observers\SupplierObserver;
use App\Services\Hubspot\Hubspot;
use Event;
use HubSpot\Client\Crm\Companies\ApiException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class SupplierObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $supplier = Supplier::factory()->make(['uuid' => null]);

        $observer = new SupplierObserver();

        $observer->creating($supplier);

        $this->assertNotNull($supplier->uuid);
    }

    /** @test
     * @throws ApiException
     */
    public function it_sync_company_to_hubspot_when_created()
    {
        $supplier = Supplier::factory()->make();

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->with($supplier)->once();
        App::bind(Hubspot::class, fn() => $hubspot);

        $observer = new SupplierObserver();
        $observer->created($supplier);
    }

    /** @test
     * @throws ApiException
     */
    public function it_sync_company_to_hubspot_when_updated()
    {
        $supplier = Supplier::factory()->make();

        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->with($supplier)->once();
        App::bind(Hubspot::class, fn() => $hubspot);

        $observer = new SupplierObserver();
        $observer->updated($supplier);
    }

    /** @test
     * @throws ApiException
     */
    public function it_creates_a_pubnub_channel_per_every_common_user_when_the_published_at_is_updated_and_the_previous_value_is_null(
    )
    {
        $supplier               = Supplier::factory()->createQuietly();
        $supplier->published_at = Carbon::now();
        $supplierUsers          = SupplierUser::factory()->usingSupplier($supplier)->count(5)->create();
        SupplierUser::factory()->count(10)->createQuietly();

        $observer = new SupplierObserver();
        $observer->updating($supplier);

        $this->assertDatabaseCount(PubnubChannel::tableName(), 5);

        $supplierUsers->each(function($supplierUser) use ($supplier) {
            $this->assertDatabaseHas(PubnubChannel::tableName(), [
                'user_id'     => $supplierUser->user->getKey(),
                'supplier_id' => $supplier->getKey(),
            ]);
        });
    }

    /** @test */
    public function it_does_not_creates_any_pubnub_channel_when_the_published_at_is_updated_and_the_previous_value_is_not_null(
    )
    {
        $supplier               = Supplier::factory()->createQuietly([
            'published_at' => Carbon::now()->subDay(),
        ]);
        $supplier->published_at = Carbon::now();
        SupplierUser::factory()->usingSupplier($supplier)->count(5)->create();
        SupplierUser::factory()->count(10)->createQuietly();

        $observer = new SupplierObserver();
        $observer->updating($supplier);

        $this->assertDatabaseCount(PubnubChannel::tableName(), 0);
    }

    /** @test */
    public function it_does_not_creates_any_pubnub_channel_when_the_published_at_is_not_updated()
    {
        $supplier = Supplier::factory()->createQuietly([
            'published_at' => Carbon::now()->subDay(),
        ]);
        SupplierUser::factory()->usingSupplier($supplier)->count(5)->create();
        SupplierUser::factory()->count(10)->createQuietly();

        $observer = new SupplierObserver();
        $observer->updating($supplier);

        $this->assertDatabaseCount(PubnubChannel::tableName(), 0);
    }

    /** @test */
    public function it_dispatches_a_created_event_when_pubnub_channel_is_created()
    {
        Event::fake([Created::class]);
        $supplier               = Supplier::factory()->createQuietly();
        $supplier->published_at = Carbon::now();
        $supplierUsers          = SupplierUser::factory()->usingSupplier($supplier)->count(3)->create();
        SupplierUser::factory()->count(2)->createQuietly();

        $observer = new SupplierObserver();
        $observer->updating($supplier);

        $supplierUsers->each(function(SupplierUSer $supplierUser) {
            Event::assertDispatched(Created::class, function(Created $event) use ($supplierUser) {
                $channelSupplierId = $event->pubnubChannel()->supplier->getKey();
                $channelUserId     = $event->pubnubChannel()->user->getKey();
                $supplierId        = $supplierUser->supplier->getKey();
                $userId            = $supplierUser->user->getKey();

                return $channelUserId == $userId && $channelSupplierId == $supplierId;
            });
        });
    }

    /** @test */
    public function it_does_not_dispatch_a_created_event_when_pubnub_channel_exist()
    {
        Event::fake([Created::class]);
        $supplier               = Supplier::factory()->createQuietly();
        $supplier->published_at = Carbon::now();
        $supplierUsers          = SupplierUser::factory()->usingSupplier($supplier)->count(3)->create();

        $supplierUsers->each(function(SupplierUser $supplierUser) {
            PubnubChannel::factory()->usingSupplier($supplierUser->supplier)->usingUser($supplierUser->user)->create();
        });

        $observer = new SupplierObserver();
        $observer->updating($supplier);

        Event::assertNothingDispatched();
    }
}
