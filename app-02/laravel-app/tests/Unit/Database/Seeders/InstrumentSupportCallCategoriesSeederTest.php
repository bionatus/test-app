<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use App\Models\Instrument;
use App\Models\Scopes\ByRouteKey;
use App\Models\SupportCallCategory;
use Arr;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\InstrumentSupportCallCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Storage;
use Tests\TestCase;

class InstrumentSupportCallCategoriesSeederTest extends TestCase
{
    use RefreshDatabase;

    const CATEGORIES    = [
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
    const INSTRUMENTS   = [
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
    const SUBCATEGORIES = [
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

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(InstrumentSupportCallCategoriesSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_the_corresponding_instruments()
    {
        Storage::fake('development_media_do');

        $seeder = new InstrumentSupportCallCategoriesSeeder();
        $seeder->run();

        foreach (self::INSTRUMENTS as $instrument) {
            $this->assertDatabaseHas(Instrument::tableName(), $instrument);
        }
    }

    /** @test */
    public function it_stores_the_corresponding_categories()
    {
        Storage::fake('development_media_do');

        $seeder = new InstrumentSupportCallCategoriesSeeder();
        $seeder->run();

        foreach (self::CATEGORIES as $category) {
            $this->assertDatabaseHas(SupportCallCategory::tableName(), Arr::except($category, 'instruments'));
        }
    }

    /** @test */
    public function it_stores_the_corresponding_subcategories()
    {
        Storage::fake('development_media_do');

        $seeder = new InstrumentSupportCallCategoriesSeeder();
        $seeder->run();

        foreach (self::SUBCATEGORIES as $categorySlug => $subcategories) {
            $category = SupportCallCategory::scoped(new ByRouteKey($categorySlug))->firstOrFail();

            foreach ($subcategories as $subcategory) {
                $subcategory['parent_id'] = $category->getKey();

                $this->assertDatabaseHas(SupportCallCategory::tableName(), Arr::except($subcategory, 'instruments'));
            }
        }
    }

    /** @test */
    public function it_runs_in_specific_environments()
    {
        $seeder   = new InstrumentSupportCallCategoriesSeeder();
        $expected = [
            Environments::LOCAL,
            Environments::DEVELOPMENT,
            Environments::QA,
            Environments::QA2,
            Environments::DEMO,
            Environments::STAGING,
            Environments::UAT,
            Environments::PRODUCTION,
        ];

        $this->assertEquals($expected, $seeder->environments());
    }
}
