<?php

namespace Tests\Unit\Jobs\OrderSnap;

use App\Jobs\OrderSnap\SaveOrderSnapInformation;
use App\Models\ItemOrder;
use App\Models\ItemOrderSnap;
use App\Models\Order;
use App\Models\OrderSnap;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SaveOrderSnapInformationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SaveOrderSnapInformation::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws \Throwable
     */
    public function it_creates_an_order_snap_with_the_items()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->pending()->create();
        $items    = ItemOrder::factory()->usingOrder($order)->create();

        $job = new SaveOrderSnapInformation($order);
        $job->handle();

        $this->assertDatabaseHas(OrderSnap::tableName(), [
            'user_id'       => $order->user_id,
            'supplier_id'   => $order->supplier_id,
            'oem_id'        => $order->oem_id,
            'name'          => $order->name,
            'working_on_it' => $order->working_on_it,
            'status'        => $order->getStatusName(),
            'bid_number'    => $order->bid_number,
            'discount'      => $order->discount,
            'tax'           => $order->tax,
        ]);

        $items->each(function(ItemOrder $itemOrder) {
            $this->assertDatabaseHas(ItemOrderSnap::tableName(), [
                'item_id'                  => $itemOrder->item_id,
                'order_id'                 => $itemOrder->order_id,
                'replacement_id'           => $itemOrder->replacement_id,
                'quantity'                 => $itemOrder->quantity,
                'price'                    => $itemOrder->price,
                'supply_detail'            => $itemOrder->supply_detail,
                'custom_detail'            => $itemOrder->custom_detail,
                'generic_part_description' => $itemOrder->generic_part_description,
                'status'                   => $itemOrder->status,
            ]);
        });
    }
}
