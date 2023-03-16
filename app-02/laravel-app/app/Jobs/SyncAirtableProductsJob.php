<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Brand;
use App\Product;
use App\Series;
use App\Lib\AirtableClient;

class SyncAirtableProductsJob implements ShouldQueue
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
        /**
         * Temporary import
         *
         */
        $this->manualDataImport();

        $this->client = $client;
        $this->client->setEndpoint(config('airtable.endpoints.products'));
        $this->client->prepare([$this, 'parseResponse']);

        if ($this->client->hasFailed()) {
            return;
        }

        $models = [
            Product::class,
            Brand::class,
            Series::class,
        ];

        foreach ($models as $model) {
            $this->clearStaleRecords($model);
        }

        return;
    }

    /**
     * Parse the received records from the Airtable
     * @param  Array $records
     * @return void
     */
    public function parseResponse($records)
    {
        // Repopulate the products table as AirTable
        // doesn't provide `last changed` check for records
        // as of the time of implementing - 01.2019
        foreach ($records as $record) {
            if (!$this->isValidRecord($record)) {
                return;
            }

            $this->updateBrand($record);
            $this->updateSeries($record);
            $this->updateProduct($record);
        }
    }

    /**
     * Checks provided record validity
     *
     * @param  array  $record
     * @return bool
     */
    protected function isValidRecord(array $record)
    {
        return count($record['fields']) > 0 && !empty(Arr::get($record, 'fields.Model'));
    }

    /**
     * Insert or update products with the
     * data from Airtable
     *
     * @param  array $productRecord
     * @return void
     */
    protected function updateProduct(array $productRecord)
    {
        $model = Arr::get($productRecord, 'fields.Model');
        $brand = Arr::get($productRecord, 'fields.Brand');

        $product = Product::firstOrNew(['model' => $model, 'brand' => $brand]);

        $product->id = (string) Str::uuid();
        $product->fields = $productRecord['fields'];

        $series = $this->getSeriesByName(Arr::get($productRecord, 'fields.Series'), Arr::get($productRecord, 'fields.Brand'));

        if ($series) {
            $product->series_id = $series->id;
        }

        $product->touch();
    }

    /**
     * Insert or update brand with the
     * data from Airtable
     *
     * @param  array $productRecord
     * @return void
     */
    protected function updateBrand(array $productRecord)
    {
        if (!($brandName = Arr::get($productRecord, 'fields.Brand'))) {
            return;
        }

        $brand = $this->getBrandByName($brandName);

        // Brand already updated - continue
        if ($brand && $brand->updated_at->gte(date('Y-m-d'))) {
            return;
        }

        if (!$brand) {
            $brand = new Brand();

            $brand->name = $brandName;
        }

        $brand->logo = Arr::get($productRecord, 'fields.Logo');

        $brand->touch();
    }

    /**
     * Insert or update series with the
     * data from Airtable
     *
     * @param  array $productRecord
     * @return void
     */
    protected function updateSeries(array $productRecord)
    {
        $seriesName = Arr::get($productRecord, 'fields.Series');
        $brandName = Arr::get($productRecord, 'fields.Brand');

        $series = $this->getSeriesByName($seriesName, $brandName);

        // Series already updated - continue
        if ($series && $series->updated_at->gte(date('Y-m-d'))) {
            return;
        }

        if (!$series) {
            $series = new Series();

            $series->name = $seriesName;
        }

        $brand = $this->getBrandByName(Arr::get($productRecord, 'fields.Brand'));

        $series->brand_id = $brand->id;
        $imageData = Arr::get($productRecord, 'fields.Unit Image');

        $image = '';

        if (empty($imageData[0]['thumbnails']) && !empty($imageData[0]['url'])) {
            $image = $imageData[0]['url'];
        } else if (!empty($imageData[0]['thumbnails'])) {
            $image = $imageData[0]['thumbnails']['large']['url'];
        }

        $series->image = $image;

        $series->touch();
    }

    /**
     * Get brand by name
     *
     * @param  string $name
     * @return Brand object
     */
    protected function getBrandByName($name)
    {
        $brand = Brand::where('name', $name)->first();

        return $brand;
    }

    /**
     * Get series by name
     *
     * @param  string $name
     * @return Series object
     */
    protected function getSeriesByName($name, $brandName)
    {
        $brand = $this->getBrandByName($brandName);

        $series = Series::where([
            'name' => $name,
            'brand_id' => $brand->id,
        ])->first();

        return $series;
    }

    /**
     * Remove records which were not returned by the API
     *
     * @return void
     */
    protected function clearStaleRecords($model) {
        $model::where('updated_at', '<', date('Y-m-d'))->delete();
    }

    /**
     * Temporary import
     *
     * @return void
     */
    protected function manualDataImport()
    {
        $file_to_import = storage_path('files/products-13-12-2021.csv');

        if (!file_exists($file_to_import)) {
            return;
        }

        $file = fopen($file_to_import, 'r');

        $map_images_fields = [
            'Logo',
            'Unit Image',
        ];

        $map_files_fields = [
            'Product Data',
            'Service Facts',
            'Diagnostic',
            'IOM',
            'Misc',
            'Wiring Diagram',
            'Bluon Guidelines',
        ];

        $comma_separated_fields = [
            'Standard Controls',
            'Optional Controls',
        ];

        $record = ['fields' => []];
        $keys = collect(fgetcsv($file, 0, ','))
            ->map(function ($key) {
                return preg_replace('/[[:^print:]]/', '', $key);
            });

        while (($line = fgetcsv($file, 0, ',')) !== false) {
            $record['fields'] = $keys
                ->combine($line)
                ->map(function ($field, $key) use ($map_images_fields, $map_files_fields, $comma_separated_fields) {

                    if( in_array( $key, $map_images_fields ) && !empty($field) ) {
                        $field = [[
                            'url' => $field,
                            'thumbnails' => [
                                'small' => [
                                    'url' => $field,
                                ],
                                'large' => [
                                    'url' => $field,
                                ],
                                'full' => [
                                    'url' => $field,
                                ]
                            ],
                        ]];
                    }

                    if( in_array( $key, $map_files_fields ) && !empty($field) ) {
                        $field = [[
                            'url' => $field,
                        ]];
                    }

                    if ((in_array( $key, $comma_separated_fields ) && !empty($field)) || $key === 'Warnings') {
                        $field = explode(',', $field);
                    }

                    return $field;
                })
                ->toArray();

            if (!$this->isValidRecord($record)) {
                return;
            }

            $this->updateBrand($record);
            $this->updateSeries($record);
            $this->updateProduct($record);
        }
    }
}
