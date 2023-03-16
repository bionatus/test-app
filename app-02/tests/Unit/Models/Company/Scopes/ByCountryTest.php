<?php

namespace Tests\Unit\Models\Company\Scopes;

use App\Models\Company;
use App\Models\Scopes\ByCountry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByCountryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_records_by_country()
    {
        Company::factory()->count(2)->create(['country' => 'US']);
        Company::factory()->count(3)->create(['country' => 'AR']);
        Company::factory()->count(4)->create(['country' => 'AU']);

        $companies = Company::scoped(new ByCountry('AR'))->get();
        $this->assertCount(3, $companies);
    }
}
