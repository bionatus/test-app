<?php

namespace Tests\Unit\Models\Oem\Scopes;

use App\Models\Oem;
use App\Models\ModelType;
use App\Models\Oem\Scopes\ByModelType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByModelTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_oems_by_model_type()
    {
        $modelType = ModelType::factory()->create(['name' => 'Package Unit']);
        Oem::factory()->count(2)->usingModelType($modelType)->create();
        Oem::factory()->count(3)->create();

        $filtered = Oem::scoped(new ByModelType($modelType))->get();

        $this->assertCount(2, $filtered);
    }
}
