<?php

namespace Tests\Unit\Actions\Models\Order;

use App\Actions\Models\Order\ChangeStatus;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;

class ChangeStatusTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    /** @test */
    public function it_changes_the_status_of_an_order()
    {
        $supplier    = Supplier::factory()->createQuietly();
        $order       = Order::factory()->pending()->usingSupplier($supplier)->create()->fresh();
        $orderResult = (new ChangeStatus($order, Substatus::STATUS_CANCELED_REJECTED))->execute();

        $this->assertEquals(Substatus::STATUS_CANCELED_REJECTED, $orderResult->lastStatus->substatus_id);
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_CANCELED_REJECTED,
        ]);
    }

    /** @test */
    public function it_changes_the_status_of_an_order_with_detail()
    {
        $supplier    = Supplier::factory()->createQuietly();
        $order       = Order::factory()->pending()->usingSupplier($supplier)->create()->fresh();
        $orderResult = (new ChangeStatus($order, Substatus::STATUS_CANCELED_CANCELED, $detail = 'detail'))->execute();

        $this->assertEquals(Substatus::STATUS_CANCELED_CANCELED, $orderResult->lastStatus->substatus_id);
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'detail'       => $detail,
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_CANCELED_CANCELED,
        ]);
    }
}
