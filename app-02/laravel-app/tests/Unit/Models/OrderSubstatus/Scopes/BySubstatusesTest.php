<?php

namespace Tests\Unit\Models\OrderSubstatus\Scopes;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\OrderSubstatus\Scopes\BySubstatuses;
use App\Models\Substatus;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySubstatusesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_order_substatus_by_substatuses()
    {
        $supplier        = Supplier::factory()->createQuietly();
        $pendingApproval = Order::factory()->pendingApproval()->usingSupplier($supplier)->count(3)->create();
        $pending         = Order::factory()->pending()->usingSupplier($supplier)->count(2)->create();
        Order::factory()->completed()->usingSupplier($supplier)->count(2)->create();
        Order::factory()->canceled()->usingSupplier($supplier)->count(2)->create();
        Order::factory()->approved()->usingSupplier($supplier)->count(2)->create();

        $filtered = OrderSubstatus::scoped(new BySubstatuses(array_merge(Substatus::STATUSES_PENDING,
            Substatus::STATUSES_PENDING_APPROVAL)))->get();
        $expected = $pendingApproval->concat($pending)->pluck('lastStatus.id');

        $this->assertCount($expected->count(), $filtered);
        $this->assertEqualsCanonicalizing($expected, $filtered->pluck('id'));
    }
}
