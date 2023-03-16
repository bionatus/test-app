<?php

namespace Tests\Unit\Models\SupplierInvitation\Scopes;

use App\Models\Supplier;
use App\Models\SupplierInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySupplierTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_supplier_id()
    {
        $supplier                    = Supplier::factory()->createQuietly();
        $expectedSupplierInvitations = SupplierInvitation::factory()->usingSupplier($supplier)->count(10)->create();
        SupplierInvitation::factory()->count(5)->createQuietly();

        $filtered = SupplierInvitation::scoped(new SupplierInvitation\Scopes\BySupplier($supplier))->get();

        $this->assertCount(10, $filtered);
        $this->assertEqualsCanonicalizing($expectedSupplierInvitations->modelKeys(), $filtered->modelKeys());
    }
}
