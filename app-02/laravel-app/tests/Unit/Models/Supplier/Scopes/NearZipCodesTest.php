<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\NearZipCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearZipCodesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_zip_code()
    {
        Supplier::factory()->count(10)->createQuietly();
        $supplier = Supplier::factory()->createQuietly(['zip_code' => '19963']);

        $supplierNearZipCode = Supplier::scoped(new NearZipCodes('19963'))->paginate();

        $this->assertSame($supplier->getRouteKey(), $supplierNearZipCode->first()->getRouteKey());
        $this->assertCount(11, $supplierNearZipCode);
    }
}
