<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\Supply;
use App\Models\SupplyCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Str;

class DemoSuppliesSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const CATEGORY_ELECTRICAL                 = 'Electrical';
    const CATEGORY_COMMON_TOOLS               = 'Common Tools';
    const SUBCATEGORY_FITTINGS                = 'Fittings';
    const SUBCATEGORY_TERMINAL_CONNECTORS     = 'Terminal Connectors';
    const SUBCATEGORY_WIRE_NUTS               = 'Wire nuts';
    const SUBCATEGORY_WIRETIES                = 'Wireties';
    const SUBCATEGORY_FUSES                   = 'Fuses';
    const SUBCATEGORY_CONTROL_WIRE            = 'Control Wire';
    const SUBCATEGORY_POWER_WIRE              = 'Power Wire';
    const SUBCATEGORY_BATTERIES               = 'Batteries';
    const SUBCATEGORY_INSTALLATION_COMPONENTS = 'Installation Components';
    const SUBCATEGORY_ACCESSORIES             = 'Accessories';
    const SUBCATEGORY_MARKING_TAPE            = 'Marking Tape';
    const CATEGORIES                          = [
        self::CATEGORY_ELECTRICAL,
        self::CATEGORY_COMMON_TOOLS,
    ];
    const SUBCATEGORIES_ELECTRICAL            = [
        self::SUBCATEGORY_FITTINGS,
        self::SUBCATEGORY_TERMINAL_CONNECTORS,
        self::SUBCATEGORY_WIRE_NUTS,
        self::SUBCATEGORY_WIRETIES,
        self::SUBCATEGORY_FUSES,
        self::SUBCATEGORY_CONTROL_WIRE,
        self::SUBCATEGORY_POWER_WIRE,
        self::SUBCATEGORY_BATTERIES,
        self::SUBCATEGORY_INSTALLATION_COMPONENTS,
        self::SUBCATEGORY_ACCESSORIES,
        self::SUBCATEGORY_MARKING_TAPE,
    ];
    const SUPPLIES_WIRE_NUTS                  = [
        'Assorted Wire Nuts',
        'Grey Wire Nuts',
        'Blue Wire Nuts',
    ];
    const SUPPLIES_WIRETIES                   = [
        'Assorted Wireties',
        'Black 4" Wireties',
        'Black 7" Wireties',
    ];
    const SUPPLIES_FUSES                      = [
        '3A Automotive Fuse',
        '5A Automotive Fuse',
        '10A Screw-in Type T Fuse',
        '15A Screw-in Type T Fuse',
    ];
    const SUPPLIES_CONTROL_WIRE               = [
        '18/2 Control Wire',
        '18/3 Control Wire',
        '18/4 Control Wire',
    ];
    const SUPPLIES_POWER_WIRE                 = [
        '#10 Stranded Black Wire',
        '#10 Stranded Red Wire',
        '#10 Stranded White Wire',
        '#12 Stranded Green Wire',
    ];
    const SUPPLIES_BATTERIES                  = [
        'AA Battery',
        'AAA Battery',
        'C Battery',
        'D Battery',
    ];
    const SUPPLIES_INSTALLATION_COMPONENTS    = [
        'OutletBox 2x4 1/2"" Knockouts',
        'OutletBox 2x4 3/4"" Knockouts',
        'SSU',
    ];
    const SUPPLIES_ACCESSORIES                = [
        'Drain Float Switch',
        'Pan Float Switch',
        'Shallow Condensate Pump-120V',
    ];
    const SUPPLIES_MARKING_TAPES              = [
        'Red Elect Marking Tape',
        'Blue Elect Marking Tape',
        'White Elect Marking Tape',
    ];
    const SUPPLIES_BLUE_TERMINAL_CONNECTORS   = [
        'Asstd Blue Term Connectors',
        'Blue Male Quick Conn Term Connectors',
        'Blue Small Ring Term Connectors',
        'Blue Medium Ring Term Connectors',
    ];
    const SUPPLIES_RED_TERMINAL_CONNECTORS    = [
        'Asstd Red Term Connectors',
        'Red Male Quick Conn Term Connectors',
        'Red Small Ring Term Connectors',
        'Red Medium Ring Term Connectors',
    ];
    const SUPPLIES_YELLOW_TERMINAL_CONNECTORS = [
        'Asstd Yellow Term Connectors',
        'Yellow Male Quick Conn Term Connectors',
        'Yellow Small Ring Term Connectors',
        'Yellow Medium Ring Term Connectors',
    ];
    const SUPPLIES_1_2_FITTINGS               = [
        '1/2" Elect NM Cable Conn Straight',
        '1/2" Elect NM Cable Conn 90',
        '1/2" Elect EMT Conn w/Setscrew',
    ];
    const SUPPLIES_3_4_FITTINGS               = [
        '3/4" Elect NM Cable Conn Straight',
        '3/4" Elect NM Cable Conn 90',
        '3/4" Elect EMT Conn w/Setscrew',
    ];
    const SUPPLIES_COMMON_TOOLS               = [
        'Multi-screwdriver',
        'Wire Stripper',
        'Multi-meter',
        'Core Remover',
        'Tape Measure',
    ];

    public function run()
    {
        $this->createCategories();
        $this->createSubcategories();

        $this->createSupplies(self::SUPPLIES_1_2_FITTINGS, self::SUBCATEGORY_FITTINGS);
        $this->createSupplies(self::SUPPLIES_3_4_FITTINGS, self::SUBCATEGORY_FITTINGS);
        $this->createSupplies(self::SUPPLIES_BLUE_TERMINAL_CONNECTORS, self::SUBCATEGORY_TERMINAL_CONNECTORS);
        $this->createSupplies(self::SUPPLIES_RED_TERMINAL_CONNECTORS, self::SUBCATEGORY_TERMINAL_CONNECTORS);
        $this->createSupplies(self::SUPPLIES_YELLOW_TERMINAL_CONNECTORS, self::SUBCATEGORY_TERMINAL_CONNECTORS);
        $this->createSupplies(self::SUPPLIES_WIRE_NUTS, self::SUBCATEGORY_WIRE_NUTS);
        $this->createSupplies(self::SUPPLIES_WIRETIES, self::SUBCATEGORY_WIRETIES);
        $this->createSupplies(self::SUPPLIES_FUSES, self::SUBCATEGORY_FUSES);
        $this->createSupplies(self::SUPPLIES_CONTROL_WIRE, self::SUBCATEGORY_CONTROL_WIRE);
        $this->createSupplies(self::SUPPLIES_POWER_WIRE, self::SUBCATEGORY_POWER_WIRE);
        $this->createSupplies(self::SUPPLIES_BATTERIES, self::SUBCATEGORY_BATTERIES);
        $this->createSupplies(self::SUPPLIES_INSTALLATION_COMPONENTS, self::SUBCATEGORY_INSTALLATION_COMPONENTS);
        $this->createSupplies(self::SUPPLIES_ACCESSORIES, self::SUBCATEGORY_ACCESSORIES);
        $this->createSupplies(self::SUPPLIES_MARKING_TAPES, self::SUBCATEGORY_MARKING_TAPE);
        $this->createSupplies(self::SUPPLIES_COMMON_TOOLS, self::CATEGORY_COMMON_TOOLS);
    }

    private function createCategories(): void
    {
        $categories = Collection::make(self::CATEGORIES);
        $categories->each(function(string $category) {
            $categoryDoesntExist = SupplyCategory::where('slug', Str::slug($category))->doesntExist();
            if ($categoryDoesntExist) {
                SupplyCategory::factory()->name($category)->create();
            }
        });
    }

    private function createSubcategories(): void
    {
        $categoryElectrical = SupplyCategory::where('slug', Str::slug(Arr::first(self::CATEGORIES)))->first();

        $subcategories = Collection::make(self::SUBCATEGORIES_ELECTRICAL);
        $subcategories->each(function(string $subcategory) use ($categoryElectrical) {
            $subcategoryDoesntExist = SupplyCategory::where('parent_id', $categoryElectrical->getKey())
                ->where('slug', Str::slug($subcategory))
                ->doesntExist();
            if ($subcategoryDoesntExist) {
                SupplyCategory::factory()->name($subcategory)->usingParent($categoryElectrical)->create();
            }
        });
    }

    private function createSupplies(array $supplies, string $category): void
    {
        $supplyCategory   = SupplyCategory::where('slug', Str::slug($category))->first();
        $suppliesFittings = Collection::make($supplies);
        $suppliesFittings->each(function(string $supply) use ($supplyCategory) {
            $supplyDoesntExist = Supply::where('name', $supply)->doesntExist();

            if ($supplyDoesntExist) {
                Supply::factory()->name($supply)->internalName($supply)->usingSupplyCategory($supplyCategory)->create();
            }
        });
    }

    public function environments(): array
    {
        return Environments::ONLY_LOCAL_AND_DEVELOPMENT;
    }
}
