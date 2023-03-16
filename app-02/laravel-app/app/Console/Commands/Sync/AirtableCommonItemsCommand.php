<?php

namespace App\Console\Commands\Sync;

use App\Jobs\Airtable\SyncCommonItems;
use Illuminate\Console\Command;

class AirtableCommonItemsCommand extends Command
{
    protected $signature   = 'sync:common_items';
    protected $description = 'Synchronizes Common Items';

    public function handle()
    {
        SyncCommonItems::dispatch()->onConnection('sync');
    }
}
