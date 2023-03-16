<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;

use App\Warning;
use App\Lib\AirtableClient;

class SyncAirtableWarningsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $client;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AirtableClient $client)
    {
        $this->client = $client;
        $this->client->setEndpoint(config('airtable.endpoints.warnings'));
        $this->client->prepare([$this, 'parseResponse']);

        $this->clearWarnings();
    }

    /**
     * Parse the received records from the Airtable
     *
     * @param  array $records
     * @return void
     */
    public function parseResponse($records)
    {
        // Repopulate the warnings table as AirTable
        // doesn't provide `last changed` check for records
        // as of the time of implementing - 01.2019
        array_map(function($record) {
            $warning = Arr::get($record, 'fields.Warning') ?? '';
            $description = Arr::get($record, 'fields.Explanation');

            if (!$warning && !$description) {
                return;
            }

            $warning = Warning::firstOrNew(['title' => $warning]);

            $warning->description = $description;
            $warning->touch();
        }, $records);
    }

    /**
     * Remove conversion jobs which were not returned by the API
     *
     * @return void
     */
    private function clearWarnings()
    {
        if ($this->client->hasFailed()) {
            // Fetching has failed - removal will be made on next iteration
            return;
        }

        Warning::where('updated_at', '<', date('Y-m-d'))->delete();
    }
}
