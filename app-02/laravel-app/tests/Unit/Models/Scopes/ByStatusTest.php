<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Scopes\ByStatus;
use App\Models\Supplier;
use App\Models\SupplierUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_status_on_supplier_user_table()
    {
        $supplier = Supplier::factory()->createQuietly();
        $expected = SupplierUser::factory()->usingSupplier($supplier)->unconfirmed()->count(2)->create();
        SupplierUser::factory()->confirmed()->count(3)->createQuietly();

        $filtered = SupplierUser::scoped(new ByStatus(SupplierUser::STATUS_UNCONFIRMED))->get();

        $this->assertInstanceOf(SupplierUser::class, $filtered->first());
        $this->assertCount(2, $filtered);
        $filtered->each(function(SupplierUser $supplierUser) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $supplierUser->getKey());
        });
    }
}
