<?php

namespace Tests\Unit\Models\Setting\Scopes;

use App\Models\Setting;
use App\Models\Setting\Scopes\ByApplicableTo;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByApplicableToTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_applicable_to()
    {
        $expectedSettings = Setting::factory()->applicableToSupplier()->count(3)->create();
        Setting::factory()->applicableToUser()->count(2)->create();

        $filtered = Setting::scoped(new ByApplicableTo(Supplier::MORPH_ALIAS))->get();

        $this->assertEqualsCanonicalizing($expectedSettings->modelKeys(), $filtered->modelKeys());
    }
}
