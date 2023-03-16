<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Constants\Filesystem;
use App\Constants\MediaCollectionNames;
use App\Models\Instrument;
use App\Models\Scopes\ByRouteKey;
use App\Models\SupportCallCategory;
use Arr;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;

class InstrumentSupportCallCategoriesSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const CATEGORIES            = [
        [
            'slug'  => 'quotes-and-ordering-parts',
            'name'  => 'Quotes & Ordering Parts',
            'phone' => '9708187304',
            'sort'  => 1,
        ],
        [
            'slug'        => 'residential',
            'name'        => 'Residential',
            'description' => 'Minisplits, HPs, ACs, Furnaces',
            'phone'       => '5026770454',
            'sort'        => 2,
        ],
        [
            'slug'        => 'commercial',
            'name'        => 'Commercial',
            'description' => '5-130+ Tons',
            'phone'       => '5037512433',
            'sort'        => 3,
        ],
        [
            'slug'        => 'chillers',
            'name'        => 'Chillers',
            'phone'       => '4803607862',
            'sort'        => 4,
            'instruments' => [
                'sh-sc-measurements',
                'gauges',
                'multimeter-voltmeter',
                'jumper-wires',
            ],
        ],
        [
            'slug'  => 'manuals-and-diagrams',
            'name'  => 'Manuals & Diagrams',
            'phone' => '2082620842',
            'sort'  => 5,
        ],
    ];
    const INSTRUMENTS           = [
        [
            'slug' => 'gauges',
            'name' => 'Gauges',
        ],
        [
            'slug' => 'jumper-wires',
            'name' => 'Jumper wires',
        ],
        [
            'slug' => 'manometer',
            'name' => 'Manometer',
        ],
        [
            'slug' => 'sh-sc-measurements',
            'name' => 'SH/SC Measurements',
        ],
        [
            'slug' => 'multimeter-voltmeter',
            'name' => 'Multimeter/Voltmeter',
        ],
    ];
    const SUBCATEGORIES         = [
        'commercial'                => [
            [
                'slug'        => 'commercial-package-unit',
                'name'        => 'Package Unit',
                'phone'       => '5037512433',
                'sort'        => 1,
                'instruments' => [
                    'sh-sc-measurements',
                    'multimeter-voltmeter',
                    'gauges',
                    'jumper-wires',
                ],
            ],
            [
                'slug'        => 'commercial-gas-electric',
                'name'        => 'Gas Electric',
                'phone'       => '6673098077',
                'sort'        => 2,
                'instruments' => [
                    'sh-sc-measurements',
                    'multimeter-voltmeter',
                    'manometer',
                    'jumper-wires',
                    'gauges',
                ],
            ],
            [
                'slug'        => 'commercial-heat-pump',
                'name'        => 'Heat Pump',
                'phone'       => '8583621645',
                'sort'        => 3,
                'instruments' => [
                    'sh-sc-measurements',
                    'multimeter-voltmeter',
                    'gauges',
                    'jumper-wires',
                ],
            ],
            [
                'slug'        => 'commercial-split-buildup',
                'name'        => 'Split/Buildup',
                'phone'       => '2134622376',
                'sort'        => 4,
                'instruments' => [
                    'sh-sc-measurements',
                    'multimeter-voltmeter',
                    'gauges',
                    'jumper-wires',
                ],
            ],
            [
                'slug'        => 'commercial-other',
                'name'        => 'Other',
                'phone'       => '8182863910',
                'sort'        => 5,
                'instruments' => [
                    'sh-sc-measurements',
                    'multimeter-voltmeter',
                    'gauges',
                    'jumper-wires',
                ],
            ],
        ],
        'quotes-and-ordering-parts' => [
            [
                'slug'  => 'quotes-and-ordering-parts-how-to-support',
                'name'  => '"How to" support',
                'phone' => '9708187304',
                'sort'  => 1,
            ],
            [
                'slug'  => 'quotes-and-ordering-parts-question-on-quote',
                'name'  => 'Question on Quote',
                'phone' => '5205422004',
                'sort'  => 2,
            ],
        ],
        'residential'               => [
            [
                'slug'        => 'residential-mini-split',
                'name'        => 'Mini Split',
                'phone'       => '5026770454',
                'sort'        => 1,
                'instruments' => [
                    'multimeter-voltmeter',
                    'gauges',
                    'jumper-wires',
                ],
            ],
            [
                'slug'        => 'residential-heat-pump',
                'name'        => 'Heat Pump',
                'phone'       => '5027541493',
                'sort'        => 2,
                'instruments' => [
                    'multimeter-voltmeter',
                    'gauges',
                    'jumper-wires',
                ],
            ],
            [
                'slug'        => 'residential-cooling-only',
                'name'        => 'Cooling Only (AC)',
                'phone'       => '5203396825',
                'sort'        => 3,
                'instruments' => [
                    'multimeter-voltmeter',
                    'gauges',
                    'jumper-wires',
                ],
            ],
            [
                'slug'        => 'residential-furnace',
                'name'        => 'Furnace',
                'phone'       => '4708914711',
                'sort'        => 4,
                'instruments' => [
                    'multimeter-voltmeter',
                    'manometer',
                    'jumper-wires',
                ],
            ],
            [
                'slug'        => 'residential-other',
                'name'        => 'Other',
                'phone'       => '3179604278',
                'sort'        => 5,
                'instruments' => [
                    'multimeter-voltmeter',
                    'gauges',
                    'jumper-wires',
                ],
            ],
        ],
    ];
    const INSTRUMENTS_DIRECTORY = Filesystem::FOLDER_DEVELOPMENT_INSTRUMENTS;
    const CATEGORIES_DIRECTORY  = Filesystem::FOLDER_DEVELOPMENT_SUPPORT_CALL_CATEGORIES;

    public function run()
    {
        foreach (self::INSTRUMENTS as $instrumentData) {
            $instrument = Instrument::updateOrCreate(['slug' => $instrumentData['slug']], $instrumentData);
            $this->addMedia($instrument, self::INSTRUMENTS_DIRECTORY);
        }

        foreach (self::CATEGORIES as $categoryData) {
            $category = $this->createCategory($categoryData);

            if (!Arr::exists(self::SUBCATEGORIES, $slug = $category->slug)) {
                continue;
            }

            foreach (self::SUBCATEGORIES[$slug] as $subcategoryData) {
                $subcategoryData['parent_id'] = $category->getKey();

                $this->createCategory($subcategoryData);
            }
        }
    }

    private function createCategory(array $data): SupportCallCategory
    {
        $category = SupportCallCategory::updateOrCreate(['slug' => $data['slug']], Arr::except($data, 'instruments'));
        $this->addInstruments($category, $data);
        $this->addMedia($category, self::CATEGORIES_DIRECTORY);

        return $category;
    }

    private function addInstruments(SupportCallCategory $category, array $data): void
    {
        if (!Arr::has($data, 'instruments')) {
            return;
        }

        foreach ($data['instruments'] as $slug) {
            $instrument = Instrument::scoped(new ByRouteKey($slug))->firstOrFail();
            $category->instruments()->syncWithoutDetaching($instrument->getKey());
        }
    }

    private function addMedia(HasMedia $model, string $directory): void
    {
        if ($model->hasMedia(MediaCollectionNames::IMAGES)) {
            return;
        }

        $file = "$directory{$model->getRouteKey()}.png";

        if (Storage::disk(Filesystem::DISK_DEVELOPMENT_MEDIA)->exists($file)) {
            try {
                $model->addMediaFromDisk($file, Filesystem::DISK_DEVELOPMENT_MEDIA)
                    ->preservingOriginal()
                    ->toMediaCollection(MediaCollectionNames::IMAGES);
            } catch (Exception $exception) {
                // Silently ignored
            }
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
