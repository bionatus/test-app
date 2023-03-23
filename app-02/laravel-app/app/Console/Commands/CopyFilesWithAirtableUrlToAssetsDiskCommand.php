<?php

namespace App\Console\Commands;

use App\Jobs\CopyFilesFromUrlToAssetsDisk;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\Part;
use App\Models\Scopes\BySearchString;
use App\Models\Series;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Str;

class CopyFilesWithAirtableUrlToAssetsDiskCommand extends Command
{
    protected $signature   = 'copy-airtable-files-to-assets-disk';
    protected $description = 'Copy the Airtable files to the assets disk';
    const FIELDS        = [
        Brand::class  => [
            'logo',
        ],
        Oem::class    => [
            'bluon_guidelines',
            'controls_manuals',
            'diagnostic',
            'iom',
            'logo',
            'misc',
            'product_data',
            'service_facts',
            'unit_image',
            'wiring_diagram',
        ],
        Part::class   => [
            'image',
        ],
        Series::class => [
            'image',
        ],

    ];
    const SEARCH_STRING = 'airtable';

    public function handle()
    {
        $fields       = self::FIELDS;
        $searchString = self::SEARCH_STRING;
        $model        = $this->choice('Choose a model', array_keys($fields));
        $bar          = $this->output->createProgressBar();

        foreach ($fields[$model] as $field) {
            $queuedUrls = [];
            $instances  = $model::select([$field])
                ->scoped(new BySearchString($searchString, $field))
                ->distinct()
                ->cursor();

            $bar->setMaxSteps($instances->count());
            $bar->start();

            foreach ($instances as $instance) {
                $attribute = $instance->getAttribute($field);
                $urls      = Arr::accessible($attribute) ? $this->getUrls($attribute) : explode(';', $attribute);
                foreach ($urls as $url) {
                    if (!Str::contains($url, $searchString) || in_array($url = trim($url), $queuedUrls)) {
                        continue;
                    }
                    $queuedUrls[] = $url;
                    dispatch(new CopyFilesFromUrlToAssetsDisk($model, $field, $url));
                }

                $bar->advance();
            }

            $bar->finish();
        }

        $bar->clear();
    }

    private function getUrls(array $logoData): array
    {
        $data = Collection::make(Arr::flatten($logoData));
        $urls = $data->filter(fn(string $item) => filter_var($item, FILTER_VALIDATE_URL));

        return $urls->unique()->values()->toArray();
    }
}
