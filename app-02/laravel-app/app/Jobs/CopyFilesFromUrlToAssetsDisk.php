<?php

namespace App\Jobs;

use App\Constants\Filesystem;
use App\Models\Model;
use App\Models\Scopes\BySearchString;
use Arr;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Log;
use Storage;
use Str;

class CopyFilesFromUrlToAssetsDisk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $field;
    /** @var Model $model */
    private string $model;
    private string $url;

    public function __construct(string $model, string $field, string $url)
    {
        $this->field = $field;
        $this->model = $model;
        $this->url   = $url;
        $this->onConnection('database');
        $this->onQueue('assets_disk');
    }

    public function handle()
    {
        $instancesCount = $this->model::scoped(new BySearchString($this->url, $this->field))->count();

        if (!$instancesCount) {
            return;
        }

        $tableName = $this->model::tableName();
        $channel   = Log::build([
            'driver' => 'single',
            'path'   => storage_path("logs/copy-airtable-files-to-assets-disk/$tableName/$this->field.log"),
        ]);
        $disk      = Storage::disk(Filesystem::DISK_ASSETS);
        $folder    = $tableName . '/' . $this->field;

        try {
            $stream = fopen($this->url, 'r');

            $fileName  = pathinfo($this->url, PATHINFO_FILENAME);
            $extension = pathinfo($this->url, PATHINFO_EXTENSION);
            $basename  = "$folder/$fileName.$extension";
            $version   = 2;

            while ($disk->exists($basename)) {
                $basename = "$folder/$fileName-$version.$extension";
                $version++;
            }

            $disk->writeStream($basename, $stream);

            $newUrl = $disk->url($basename);

            DB::enableQueryLog();

            $this->model::scoped(new BySearchString($this->url, $this->field))->update([
                $this->field => DB::raw("REPLACE($this->field,  '$this->url', '$newUrl')"),
            ]);

            $queries = DB::getQueryLog();
            $this->logQuery($queries);
            DB::flushQueryLog();
            DB::disableQueryLog();
        } catch (Exception $exception) {
            @fclose($stream);
            Log::stack([$channel])->error("$this->url - {$exception->getMessage()}");
        }
    }

    private function logQuery(array $queries): void
    {
        $query              = Arr::first($queries);
        $sqlWithoutBindings = $query['query'];
        $bindings           = Collection::make($query['bindings'])->map(fn(string $value) => '\'' . $value . '\'');
        $sqlWithBindings    = Str::replaceArray('?', $bindings->toArray(), $sqlWithoutBindings) . ';';
        Storage::disk(Filesystem::DISK_EXPORTS)->append('queries-script-airtable-files.sql', $sqlWithBindings);
    }
}
