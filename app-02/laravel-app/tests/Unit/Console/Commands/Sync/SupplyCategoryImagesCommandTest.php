<?php

namespace Tests\Unit\Console\Commands\Sync;

use App\Jobs\SupplyCategory\SyncImages;
use Bus;
use Tests\TestCase;

class SupplyCategoryImagesCommandTest extends TestCase
{
    /** @test */
    public function it_dispatch_sync_suppliers_job()
    {
        Bus::fake([SyncImages::class]);

        $this->artisan('sync:supply-category-images')->assertSuccessful();

        Bus::assertDispatched(SyncImages::class);
    }
}
