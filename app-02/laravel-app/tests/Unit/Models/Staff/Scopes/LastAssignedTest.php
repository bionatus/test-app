<?php

namespace Tests\Unit\Models\Staff\Scopes;

use App\Models\OrderStaff;
use App\Models\Staff;
use App\Models\Staff\Scopes\LastAssigned;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LastAssignedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_last_staff_assigned_to_an_order()
    {
        $now = Carbon::now();
        $supplier = Supplier::factory()->createQuietly();
        $otherStaff = Staff::factory()->usingSupplier($supplier)->create();
        $lastAssignedStaff = Staff::factory()->usingSupplier($supplier)->create();
        Staff::factory()->usingSupplier($supplier)->count(5)->create();
        Carbon::setTestNow($now->clone()->subDays(2));
        OrderStaff::factory()->usingStaff($otherStaff)->create();
        Carbon::setTestNow($now);
        OrderStaff::factory()->usingStaff($lastAssignedStaff)->create();

        $result = Staff::scoped(new LastAssigned())->first();
        $this->assertSame($lastAssignedStaff->getKey(), $result->getKey());
    }
}
