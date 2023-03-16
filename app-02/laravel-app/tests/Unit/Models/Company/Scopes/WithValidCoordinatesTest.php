<?php

namespace Tests\Unit\Models\Company\Scopes;

use App\Models\Company;
use App\Models\Company\Scopes\WithValidCoordinates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithValidCoordinatesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_records_without_valid_coordinates()
    {
        Company::withoutEvents(function() {
            Company::factory()->count(2)->create(['latitude' => null, 'longitude' => 0]);
            Company::factory()->count(3)->create(['latitude' => 0, 'longitude' => null]);
            Company::factory()->count(4)->create(['latitude' => 91, 'longitude' => 0]);
            Company::factory()->count(5)->create(['latitude' => 0, 'longitude' => 181]);
            Company::factory()->count(6)->create(['latitude' => 0, 'longitude' => 0]);
        });

        $companies = Company::scoped(new WithValidCoordinates())->get();
        $this->assertCount(6, $companies);
        $companies->each(function(Company $company) {
            $this->assertSame('0', $company->latitude);
            $this->assertSame('0', $company->longitude);
        });
    }
}
