<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\ByInPendingApprovalInLastWeek;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ByInPendingApprovalInLastWeekTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_orders_which_last_status_is_fulfilled_or_quote_updated_and_that_status_is_older_than_one_hour(
    )
    {
        $supplier   = Supplier::factory()->createQuietly();
        $firstOrder = Order::factory()->pending()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()
            ->usingOrder($firstOrder)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create(['created_at' => Carbon::now()->subMinutes(61)]);
        $secondOrder = Order::factory()->pending()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()
            ->usingOrder($secondOrder)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED)
            ->create(['created_at' => Carbon::now()->subMinutes(61)]);

        OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_COMPLETED_DONE)
            ->count(2)
            ->create(['created_at' => Carbon::now()->subMinutes(61)]);
        OrderSubstatus::factory()->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)->create([
            'created_at' => Carbon::now()->subMinutes(61),
        ]);
        OrderSubstatus::factory()->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)->count(3)->create([
            'created_at' => Carbon::now()->subMinutes(59),
        ]);

        $expected          = Collection::make([$firstOrder, $secondOrder]);
        $expectedRouteKeys = $expected->pluck(Order::keyName());

        $filtered          = Order::scoped(new ByInPendingApprovalInLastWeek())->get();
        $filteredRouteKeys = $filtered->pluck(Order::keyName());

        $this->assertCount(2, $filtered);
        $this->assertEqualsCanonicalizing($expectedRouteKeys, $filteredRouteKeys);
    }

    /** @test */
    public function it_filters_orders_which_last_status_is_fulfilled_or_quote_updated_and_that_status_is_less_than_one_week(
    )
    {
        $supplier   = Supplier::factory()->createQuietly();
        $firstOrder = Order::factory()->pending()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()
            ->usingOrder($firstOrder)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)
            ->create([
                'created_at' => Carbon::now()->subDays(3),
            ]);
        $secondOrder = Order::factory()->pending()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()
            ->usingOrder($secondOrder)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED)
            ->create([
                'created_at' => Carbon::now()->subDays(5),
            ]);

        OrderSubstatus::factory()->usingSubstatusId(Substatus::STATUS_COMPLETED_DONE)->count(2)->create([
            'created_at' => Carbon::now()->subWeek()->addSecond(),
        ]);
        OrderSubstatus::factory()->usingSubstatusId(Substatus::STATUS_PENDING_REQUESTED)->count(2)->create();
        OrderSubstatus::factory()->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)->create([
            'created_at' => Carbon::now()->subDays(3),
        ]);
        OrderSubstatus::factory()->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_FULFILLED)->count(2)->create([
            'created_at' => Carbon::now()->subWeek(),
        ]);

        $expected          = Collection::make([$firstOrder, $secondOrder]);
        $expectedRouteKeys = $expected->pluck(Order::keyName());

        $filtered          = Order::scoped(new ByInPendingApprovalInLastWeek())->get();
        $filteredRouteKeys = $filtered->pluck(Order::keyName());

        $this->assertCount(2, $filtered);
        $this->assertEqualsCanonicalizing($expectedRouteKeys, $filteredRouteKeys);
    }
}
