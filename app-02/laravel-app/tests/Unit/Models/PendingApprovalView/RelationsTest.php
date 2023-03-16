<?php

namespace Tests\Unit\Models\PendingApprovalView;

use App\Models\Order;
use App\Models\PendingApprovalView;
use App\Models\Supplier;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

class RelationsTest extends RelationsTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $supplier = Supplier::factory()->createQuietly();
        $order = Order::factory()->pendingApproval()->usingSupplier($supplier)->create();
        $this->instance = PendingApprovalView::factory()->usingOrder($order)->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_order()
    {
        $related = $this->instance->order()->first();

        $this->assertInstanceOf(Order::class, $related);
    }
}
