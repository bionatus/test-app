<?php

namespace Database\Seeders;

use App\Constants\Environments;
use Database\Seeders\SOP\BrandSeriesGenerator;
use Database\Seeders\SOP\ItemPartGenerator;
use Database\Seeders\SOP\OemGenerator;
use Exception;
use Illuminate\Database\Seeder;

class DemoSOPSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    public function environments(): array
    {
        return Environments::ONLY_LOCAL_AND_DEVELOPMENT;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $brandSeriesHelper = new BrandSeriesGenerator();
        $brandSeriesHelper->createBrandsAndSeries();

        $oemHelper = new OemGenerator($brandSeriesHelper->getSeries());
        $oemHelper->createOems();

        $oemList        = $oemHelper->getOems()->groupBy(function($item) {
            return $item->series->brand->name;
        });
        $itemPartHelper = new ItemPartGenerator($brandSeriesHelper->getBrands(), $oemList);
        $itemPartHelper->createItemsParts();
    }
}
