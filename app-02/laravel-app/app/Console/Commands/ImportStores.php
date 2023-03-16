<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Store;

class ImportStores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:stores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Stores from CSV file';

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
     * @return int
     */
    public function handle()
    {

        $file = fopen(storage_path('files/locations.csv'), 'r');

        while (($filedata = fgetcsv($file, 1000, ',')) !== false) {
            if (!empty($filedata[14]) && file_exists(storage_path('files/' . $filedata[14]))) {
                $image = file_get_contents(storage_path('files/' . $filedata[14]));

                Storage::disk('public')->put($filedata[14], $image);
            }

            $store = new Store([
                'name' => $filedata[0],
                'address' => $filedata[1],
                'address2' => $filedata[2],
                'city' => $filedata[3],
                'state' => $filedata[4],
                'zip' => $filedata[5],
                'country_iso' => $filedata[7],
                'phone' => $filedata[10],
                'fax' => $filedata[11],
                'email' => $filedata[12],
                'url' => $filedata[13],
                'image' => $filedata[14],
                'lat' => $filedata[8],
                'lng' => $filedata[9],
            ]);

            $store->save();
        }
        return 0;
    }
}
