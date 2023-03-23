<?php

namespace Tests\Unit\Models\Series\Scopes;

use App\Models\Oem;
use App\Models\Series;
use App\Models\ModelType;
use App\Models\Series\Scopes\ByOemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByOemTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_series_by_oem_type()
    {
        $modelType = ModelType::factory()->create(['name' => 'Package Unit']);
        Oem::factory()->count(2)->usingModelType($modelType)->create();
        Oem::factory()->count(3)->create();

        $filtered = Series::scoped(new ByOemType($modelType))->get();

        $this->assertCount(2, $filtered);
    }
}
