<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Constants\Filesystem;
use App\Constants\MediaCollectionNames;
use App\Constants\RelationsMorphs;
use App\Models\PlainTag;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlainTagsSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const TYPE_NAME_LIST = [
        PlainTag::TYPE_ISSUE => [
            'Airflow',
            'Baselining',
            'Belts & Pulleys',
            'Brazing',
            'Capacitors',
            'Charging & Tuning',
            'Coils',
            'Compressors',
            'Contactors',
            'Contaminants',
            'Controls',
            'Crankcase Heaters',
            'Electrical & Wiring',
            'Fans',
            'Fault Codes',
            'Filter Driers/Cores',
            'Gaskets/Seals',
            'Heat Exchangers',
            'Heat Pumps',
            'Heating',
            'Hot Gas Bypass',
            'Humidification',
            'Igniters',
            'Installation',
            'Leaks',
            'Load',
            'Low Ambient',
            'Metering Devices',
            'Oil',
            'Recovery/Evacuation',
            'Restrictions',
            'Safety Devices',
            'Start Up',
            'Thermostats',
            'VFDs',
            'Zoning',
        ],
        PlainTag::TYPE_MORE  => [
            'Memes',
            'Rants',
            'Shit Jobs',
            'Other',
            'Announcements',
        ],
    ];
    const DIRECTORIES    = [
        PlainTag::TYPE_ISSUE => Filesystem::FOLDER_DEVELOPMENT_ISSUES_IMAGES,
        PlainTag::TYPE_MORE  => Filesystem::FOLDER_DEVELOPMENT_MORE_IMAGES,
    ];

    public function __construct()
    {
        Relation::morphMap(RelationsMorphs::MAP);
    }

    public function run()
    {
        foreach (self::TYPE_NAME_LIST as $type => $names) {
            $directory = self::DIRECTORIES[$type];
            foreach ($names as $name) {
                $data     = ['type' => $type, 'name' => $name];
                $plainTag = PlainTag::firstOrCreate($data, $data);

                if ($plainTag->hasMedia(MediaCollectionNames::IMAGES)) {
                    continue;
                }

                $fileName = Str::slug(str_replace('/', ' ', $name)) . '.png';
                $file     = $directory . $fileName;
                if (Storage::disk(Filesystem::DISK_DEVELOPMENT_MEDIA)->exists($file)) {
                    try {
                        $plainTag->addMediaFromDisk($file, Filesystem::DISK_DEVELOPMENT_MEDIA)
                            ->preservingOriginal()
                            ->toMediaCollection(MediaCollectionNames::IMAGES);
                    } catch (Exception $exception) {
                        // Silently ignored
                    }
                }
            }
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
