<?php

namespace App\Console\Commands\Sync;

use App\Jobs\Airtable\SyncSuppliers;
use Illuminate\Console\Command;

class AirtableSuppliersCommand extends Command
{
    protected $signature   = 'sync:suppliers {--U|update-coordinates : Update stores coordinates}';
    protected $description = 'Synchronizes suppliers from airtable';

    public function handle()
    {
        SyncSuppliers::dispatch($this->option('update-coordinates'))->onQueue('sync');
    }
}
