<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use App\Models\Supply;
use App\Models\SupplyCategory;
use Database\Seeders\DemoSuppliesSeeder;
use Database\Seeders\EnvironmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use ReflectionClass;
use Str;
use Tests\TestCase;

class DemoSuppliesSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(DemoSuppliesSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_categories()
    {
        $seeder = new DemoSuppliesSeeder();
        $seeder->run();

        $categories = Collection::make([
            'Electrical',
            'Common Tools',
        ]);

        $categories->each(function($category) {
            $this->assertDatabaseHas(SupplyCategory::tableName(), [
                'slug'      => Str::slug($category),
                'name'      => $category,
                'parent_id' => null,
            ]);
        });
    }

    /** @test */
    public function it_stores_all_subcategories_of_a_category()
    {
        $seeder = new DemoSuppliesSeeder();
        $seeder->run();

        $categoryName  = 'Electrical';
        $subcategories = Collection::make([
            'Wire nuts',
            'Wireties',
            'Terminal Connectors',
            'Fuses',
            'Control Wire',
            'Power Wire',
            'Batteries',
            'Fittings',
            'Installation Components',
            'Accessories',
            'Marking Tape',
        ]);

        $category = SupplyCategory::where('slug', Str::slug($categoryName))->first();

        $subcategories->each(function($subcategory) use ($category) {
            $this->assertDatabaseHas(SupplyCategory::tableName(), [
                'slug'      => Str::slug($subcategory),
                'name'      => $subcategory,
                'parent_id' => $category->getKey(),
            ]);
        });
    }

    /** @test */
    public function it_stores_supplies_without_type()
    {
        $seeder = new DemoSuppliesSeeder();
        $seeder->run();

        $supplies = Collection::make([
            'Assorted Wire Nuts',
            'Grey Wire Nuts',
            'Blue Wire Nuts',
            'Assorted Wireties',
            'Black 4" Wireties',
            'Black 7" Wireties',
            '3A Automotive Fuse',
            '5A Automotive Fuse',
            '10A Screw-in Type T Fuse',
            '15A Screw-in Type T Fuse',
            '18/2 Control Wire',
            '18/3 Control Wire',
            '18/4 Control Wire',
            '#10 Stranded Black Wire',
            '#10 Stranded Red Wire',
            '#10 Stranded White Wire',
            '#12 Stranded Green Wire',
            'AA Battery',
            'AAA Battery',
            'C Battery',
            'D Battery',
            'OutletBox 2x4 1/2"" Knockouts',
            'OutletBox 2x4 3/4"" Knockouts',
            'SSU',
            'Drain Float Switch',
            'Pan Float Switch',
            'Shallow Condensate Pump-120V',
            'Red Elect Marking Tape',
            'Blue Elect Marking Tape',
            'White Elect Marking Tape',
        ]);

        $supplies->each(function($supply) {
            $this->assertDatabaseHas(Supply::tableName(), [
                'name'          => $supply,
                'internal_name' => $supply,
            ]);
        });
    }

    /** @test */
    public function it_stores_supplies_with_the_correct_category()
    {
        $seeder = new DemoSuppliesSeeder();
        $seeder->run();

        $supply         = Supply::where('name', 'Assorted Wire Nuts')->first();
        $supplyCategory = $supply->supplyCategory;

        $this->assertSame('wire-nuts', $supplyCategory->slug);
    }

    /** @test */
    public function it_stores_supplies_of_a_category()
    {
        $seeder = new DemoSuppliesSeeder();
        $seeder->run();

        $supplies = Collection::make([
            'Multi-screwdriver',
            'Wire Stripper',
            'Multi-meter',
            'Core Remover',
            'Tape Measure',
        ]);

        $categoryName = 'Common Tools';
        $category     = SupplyCategory::where('slug', Str::slug($categoryName))->first();

        $supplies->each(function(string $supply) use ($category) {
            $this->assertDatabaseHas(Supply::tableName(), [
                'supply_category_id' => $category->getKey(),
                'name'               => $supply,
                'internal_name'      => $supply,
            ]);
        });
    }

    /** @test */
    public function it_runs_in_specific_environments()
    {
        $seeder   = new DemoSuppliesSeeder();
        $expected = [
            Environments::LOCAL,
            Environments::DEVELOPMENT,
        ];

        $this->assertEquals($expected, $seeder->environments());
    }
}
