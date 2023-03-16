<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Constants\Filesystem;
use App\Constants\MediaCollectionNames;
use App\Models\ModelType;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModelTypesSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const MODEL_TYPES_LIST = [
        'Package Unit',
        'Condensers',
        'Mini-Splits',
        'Furnaces',
        'Chillers',
        'Air Handlers',
        'VRFs',
        'Boilers - More Coming!',
        'Refrigeration',
        'Thermostats',
        'Others',
    ];
    const DIRECTORY        = Filesystem::FOLDER_DEVELOPMENT_MODEL_TYPES_IMAGES;

    public function run()
    {
        foreach (self::MODEL_TYPES_LIST as $name) {
            $modelType = ModelType::firstOrCreate(['name' => $name]);

            if ($modelType->hasMedia(MediaCollectionNames::IMAGES)) {
                continue;
            }

            $fileName = Str::slug(str_replace('/', ' ', $name)) . '.png';
            $file     = self::DIRECTORY . $fileName;

            if (Storage::disk(Filesystem::DISK_DEVELOPMENT_MEDIA)->exists($file)) {
                try {
                    $modelType->addMediaFromDisk($file, Filesystem::DISK_DEVELOPMENT_MEDIA)
                        ->preservingOriginal()
                        ->toMediaCollection(MediaCollectionNames::IMAGES);
                } catch (Exception $exception) {
                    // Silently ignored
                }
            }

            $modelType->save();
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
