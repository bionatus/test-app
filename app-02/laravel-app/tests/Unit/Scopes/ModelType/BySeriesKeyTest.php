<?php

namespace Tests\Unit\Scopes\ModelType;

use App\Models\ModelType;
use App\Models\Oem;
use App\Models\Series;
use App\Scopes\ModelType\BySeriesKey;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySeriesKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_series_id()
    {
        ModelType::factory()->count(5)->create();
        $modelType = ModelType::factory()->create();
        $series    = Series::factory()->create();
        Oem::factory()->usingSeries($series)->usingModelType($modelType)->create();

        $scope = new BySeriesKey($series->getRouteKey());
        $query = DB::table(ModelType::tablename());
        $scope->apply($query);

        $this->assertCount(1, $query->get());
    }
}
