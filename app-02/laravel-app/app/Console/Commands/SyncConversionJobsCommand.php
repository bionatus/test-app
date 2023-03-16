<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\SyncAirtableConversionsJob;

class SyncConversionJobsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:conversion-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes the stored conversion jobs from airtable';

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
        dispatch(new SyncAirtableConversionsJob);
    }
}
