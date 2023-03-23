<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;

use App\ConversionJob;
use App\Lib\AirtableClient;

class SyncAirtableConversionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $client;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AirtableClient $client)
    {
        $this->client = $client;
        $this->client->setEndpoint(config('airtable.endpoints.conversions'));
        $this->client->prepare([$this, 'parseResponse']);

        $this->clearConversionJobs();
    }

    /**
     * Parse the received records from the Airtable
     *
     * @param  array $records
     * @return void
     */
    public function parseResponse($records)
    {
        // Repopulate the conversion jobs table as AirTable
        // doesn't provide `last changed` check for records
        // as of the time of implementing - 01.2019
        array_map(function($record) {
            $control = Arr::get($record, 'fields.Control');

            $conversionJob = ConversionJob::firstOrNew(['control' => $control]);

            $conversionJob->standard = Arr::get($record, 'fields.Standard');
            $conversionJob->optional = Arr::get($record, 'fields.Optional');
            $conversionJob->touch();
        }, $records);
    }

    /**
     * Remove conversion jobs which were not returned by the API
     *
     * @return void
     */
    private function clearConversionJobs()
    {
        if ($this->client->hasFailed()) {
            // Fetching has failed - removal will be made on next iteration
            return;
        }

        ConversionJob::where('updated_at', '<', date('Y-m-d'))->delete();
    }
}
