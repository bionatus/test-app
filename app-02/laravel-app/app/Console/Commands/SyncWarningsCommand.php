<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\SyncAirtableWarningsJob;

class SyncWarningsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:warnings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes the stored warnings from airtable';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dispatch(new SyncAirtableWarningsJob);
    }
}
