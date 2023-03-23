<?php

namespace Tests\Unit\Models\Phone\Scopes;

use App\Models\Phone;
use App\Models\Phone\Scopes\ByCountryCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByCountryCodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_country_code()
    {
        $countryCode = '0';

        Phone::factory()->count(2)->create();
        Phone::factory()->count(3)->create(['country_code' => $countryCode]);

        $phones = Phone::scoped(new ByCountryCode($countryCode))->get();

        $this->assertCount(3, $phones);
    }
}
