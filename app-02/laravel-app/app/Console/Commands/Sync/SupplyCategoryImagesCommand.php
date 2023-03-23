<?php

namespace App\Console\Commands\Sync;

use App\Jobs\SupplyCategory\SyncImages;
use Illuminate\Console\Command;

class SupplyCategoryImagesCommand extends Command
{
    protected $signature   = 'sync:supply-category-images';
    protected $description = 'Synchronizes images for supply category from development media disk';

    public function handle()
    {
        SyncImages::dispatch()->onQueue('sync');
    }
}
