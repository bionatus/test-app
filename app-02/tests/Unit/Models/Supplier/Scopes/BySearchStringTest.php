<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\BySearchString;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySearchStringTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_partial_match_in_name()
    {
        $expectedSuppliers = Supplier::factory()->count(3)->createQuietly(['name' => 'expected name']);
        Supplier::factory()->count(10)->createQuietly();

        $filtered = Supplier::scoped(new BySearchString('cted na'))->get();

        $this->assertCount($expectedSuppliers->count(), $filtered);
    }

    /** @test */
    public function it_filters_by_partial_match_in_address()
    {
        $expectedSuppliers = Supplier::factory()->count(3)->createQuietly(['address' => 'expected address']);
        Supplier::factory()->count(10)->createQuietly();

        $filtered = Supplier::scoped(new BySearchString('cted ad'))->get();

        $this->assertCount($expectedSuppliers->count(), $filtered);
    }

    /** @test */
    public function it_filters_by_partial_match_in_city()
    {
        $expectedSuppliers = Supplier::factory()->count(3)->createQuietly(['city' => 'expected city']);
        Supplier::factory()->count(10)->createQuietly();

        $filtered = Supplier::scoped(new BySearchString('cted ci'))->get();

        $this->assertCount($expectedSuppliers->count(), $filtered);
    }

    /** @test */
    public function it_filters_by_partial_match_in_zip_code()
    {
        $expectedSuppliers = Supplier::factory()->count(3)->createQuietly(['zip_code' => '12345']);
        Supplier::factory()->count(10)->createQuietly();

        $filtered = Supplier::scoped(new BySearchString('234'))->get();

        $this->assertCount($expectedSuppliers->count(), $filtered);
    }
}
