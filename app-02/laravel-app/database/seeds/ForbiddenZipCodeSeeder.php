<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Constants\Filesystem;
use App\Models\ForbiddenZipCode;
use DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Storage;

class ForbiddenZipCodeSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const TRANSACTION_RECORDS = 250;
    const FILENAME            = 'forbidden_zip_codes.csv';

    public function run()
    {
        DB::table(ForbiddenZipCode::tableName())->truncate();

        $filename = Storage::disk(Filesystem::DISK_FILES)->path(self::FILENAME);
        $toInsert = Collection::make();

        LazyCollection::make(function() use ($filename) {
            $file = fopen($filename, 'r');
            while ($data = fgetcsv($file)) {
                yield $data;
            }
        })->each(function(array $row) use (&$toInsert) {
            $zipCode = Arr::first($row);

            $toInsert->add([
                'zip_code'   => $zipCode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if ($toInsert->count() === self::TRANSACTION_RECORDS) {
                ForbiddenZipCode::insert($toInsert->toArray());
                $toInsert = Collection::make();
            }
        });

        if ($toInsert->isNotEmpty()) {
            ForbiddenZipCode::insert($toInsert->toArray());
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}

