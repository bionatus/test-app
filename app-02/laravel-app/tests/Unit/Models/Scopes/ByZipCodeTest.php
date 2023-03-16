<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Company;
use App\Models\Scopes\ByZipCode;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByZipCodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_zip_code_on_supplier_model()
    {
        $zipCode = '123456';

        Supplier::factory()->count(2)->createQuietly();
        Supplier::factory()->count(3)->createQuietly(['zip_code' => $zipCode]);

        $stores = Supplier::scoped(new ByZipCode($zipCode))->get();

        $this->assertCount(3, $stores);
    }

    /** @test */
    public function it_filters_by_zip_code_on_company_model()
    {
        $zipCode = '123456';

        Company::factory()->count(2)->create();
        Company::factory()->count(3)->create(['zip_code' => $zipCode]);

        $stores = Company::scoped(new ByZipCode($zipCode))->get();

        $this->assertCount(3, $stores);
    }
}
