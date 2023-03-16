<?php

namespace Tests\Unit\Models\SupplierHour\Scopes;

use App\Models\SupplierHour;
use App\Models\SupplierHour\Scopes\ExceptDays;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ExceptDaysTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_excludes_days()
    {
        $expectedStoreHours = SupplierHour::factory()->monday()->count(10)->createQuietly();
        SupplierHour::factory()->tuesday()->count(5)->createQuietly();
        SupplierHour::factory()->wednesday()->count(3)->createQuietly();

        $filtered = SupplierHour::scoped(new ExceptDays(Collection::make(['tuesday', 'wednesday'])))->get();

        $this->assertEqualsCanonicalizing($expectedStoreHours->modelKeys(), $filtered->modelKeys());
    }
}
