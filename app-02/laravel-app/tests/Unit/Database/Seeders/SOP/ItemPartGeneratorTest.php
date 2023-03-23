<?php

namespace Tests\Unit\Database\Seeders\SOP;

use App\Models\Brand;
use App\Models\Item;
use App\Models\Oem;
use App\Models\Scopes\ByUuid;
use App\Models\Series;
use Database\Seeders\SOP\ItemPartGenerator;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Str;
use Tests\TestCase;

class ItemPartGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws Exception
     */
    public function it_generated_items_and_parts()
    {
        $brand1  = Brand::factory()->create(['name' => 'fake brand 1']);
        $brand2  = Brand::factory()->create(['name' => 'fake brand 2']);
        $series1 = Series::factory()->usingBrand($brand1)->create();
        $series2 = Series::factory()->usingBrand($brand2)->create();
        $brands  = Collection::make([$brand1->getKey() => $brand1, $brand2->getKey() => $brand2]);
        $oem1    = Oem::factory()->usingSeries($series1)->create();
        $oem2    = Oem::factory()->usingSeries($series2)->create();
        $oems    = Collection::make([$brand1->name => [$oem1], $brand2->name => [$oem2]]);

        $ItemPartHelper = new ItemPartGenerator($brands, $oems);
        $ItemPartHelper->createItemsParts();

        $partTypeClasses = ItemPartGenerator::TYPE_CLASSES;

        foreach (array_keys($partTypeClasses) as $type) {
            $brands->each(function($brand) use ($type, $partTypeClasses) {
                $itemUuid = Str::uuidFromString($brand->slug . '-' . $type);
                $item     = Item::scoped(new ByUuid($itemUuid))->first();
                $this->assertNotNull($item);
                $part = $item->part;
                $this->assertNotNull($part);
            });

            $this->assertDatabaseCount(($partTypeClasses[$type])::tableName(), $brands->count());
        }

        $this->assertDatabaseCount('items', count($partTypeClasses) * $brands->count());
        $this->assertDatabaseCount('parts', count($partTypeClasses) * $brands->count());
    }
}
