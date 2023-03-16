<?php

namespace Tests\Unit\Console\Commands\Sync;

use App\Jobs\Airtable\SyncCommonItems;
use Bus;
use Tests\TestCase;

class AirtableCommonItemsCommandTest extends TestCase
{
    /** @test */
    public function it_dispatch_sync_common_items_job()
    {
        Bus::fake([SyncCommonItems::class]);

        $this->artisan('sync:common_items')->assertSuccessful();

        Bus::assertDispatched(SyncCommonItems::class);
    }
}
