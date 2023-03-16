<?php

namespace App\Console\Commands\Sync;

use App\Jobs\Supplier\SyncStaff;
use Illuminate\Console\Command;

class StaffCommand extends Command
{
    protected $signature   = 'sync:staff';
    protected $description = 'Synchronizes staff manager from suppliers';

    public function handle()
    {
        SyncStaff::dispatch()->onQueue('sync');
    }
}
