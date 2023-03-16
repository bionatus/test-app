<?php

namespace Tests\Unit\Console\Commands\Invoices;

use App;
use App\Actions\Models\Order\ProcessInvoiceOnCanceledOrder as ProcessInvoiceOnCanceledOrderAction;
use App\Console\Commands\Invoices\CreateCreditForCanceledOrdersWithProcessedInvoice;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/** @see CreateCreditForCanceledOrdersWithProcessedInvoice */
class CreateCreditForCanceledOrdersWithProcessedInvoiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @dataProvider dataProvider
     */
    public function it_executes_the_action_process_invoice_on_canceled_order_just_for_canceled_orders_with_invoice_and_without_credit(
        int $substatusId,
        int $times
    ) {
        $processInvoiceOnCanceledOrderAction = Mockery::mock(ProcessInvoiceOnCanceledOrderAction::class);
        $processInvoiceOnCanceledOrderAction->shouldReceive('execute')->times($times)->andReturnNull();
        App::bind(ProcessInvoiceOnCanceledOrderAction::class, fn() => $processInvoiceOnCanceledOrderAction);

        $supplier       = Supplier::factory()->createQuietly();
        $orderProcessed = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($orderProcessed)->usingSubstatusId($substatusId)->create();
        OrderInvoice::factory()->invoice()->processed()->usingOrder($orderProcessed)->create();
        OrderInvoice::factory()->credit()->processed()->usingOrder($orderProcessed)->create();

        $orderNotProcessed = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($orderNotProcessed)->usingSubstatusId($substatusId)->create();
        OrderInvoice::factory()->invoice()->notProcessed()->usingOrder($orderNotProcessed)->create();
        OrderInvoice::factory()->credit()->notProcessed()->usingOrder($orderNotProcessed)->create();

        $orderWithInvoiceProcessedAndCreditNotProcessed = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()
            ->usingOrder($orderWithInvoiceProcessedAndCreditNotProcessed)
            ->usingSubstatusId($substatusId)
            ->create();
        OrderInvoice::factory()
            ->invoice()
            ->processed()
            ->usingOrder($orderWithInvoiceProcessedAndCreditNotProcessed)
            ->create();
        OrderInvoice::factory()
            ->credit()
            ->notProcessed()
            ->usingOrder($orderWithInvoiceProcessedAndCreditNotProcessed)
            ->create();

        $orderWithInvoiceNotProcessedAndCreditProcessed = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()
            ->usingOrder($orderWithInvoiceNotProcessedAndCreditProcessed)
            ->usingSubstatusId($substatusId)
            ->create();
        OrderInvoice::factory()
            ->invoice()
            ->notProcessed()
            ->usingOrder($orderWithInvoiceNotProcessedAndCreditProcessed)
            ->create();
        OrderInvoice::factory()
            ->credit()
            ->processed()
            ->usingOrder($orderWithInvoiceNotProcessedAndCreditProcessed)
            ->create();

        $orderWithInvoiceProcessed = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($orderWithInvoiceProcessed)->usingSubstatusId($substatusId)->create();
        OrderInvoice::factory()->invoice()->processed()->usingOrder($orderWithInvoiceProcessed)->create();

        $orderWithInvoiceNotProcessed = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($orderWithInvoiceNotProcessed)->usingSubstatusId($substatusId)->create();
        OrderInvoice::factory()->invoice()->notProcessed()->usingOrder($orderWithInvoiceNotProcessed)->create();

        $orderWithCreditProcessed = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($orderWithCreditProcessed)->usingSubstatusId($substatusId)->create();
        OrderInvoice::factory()->credit()->processed()->usingOrder($orderWithCreditProcessed)->create();

        $orderWithCreditNotProcessed = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($orderWithCreditNotProcessed)->usingSubstatusId($substatusId)->create();
        OrderInvoice::factory()->credit()->notProcessed()->usingOrder($orderWithCreditNotProcessed)->create();

        // order without invoice or credit
        $order = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $this->artisan('invoices:create-credit-for-canceled-orders')->assertSuccessful();
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, 0],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, 0],
            [Substatus::STATUS_APPROVED_DELIVERED, 0],
            [Substatus::STATUS_CANCELED_ABORTED, 2],
            [Substatus::STATUS_CANCELED_CANCELED, 2],
            [Substatus::STATUS_CANCELED_DECLINED, 2],
            [Substatus::STATUS_CANCELED_REJECTED, 2],
            [Substatus::STATUS_CANCELED_BLOCKED_USER, 2],
            [Substatus::STATUS_CANCELED_DELETED_USER, 2],
            [Substatus::STATUS_COMPLETED_DONE, 0],
            [Substatus::STATUS_PENDING_REQUESTED, 0],
            [Substatus::STATUS_PENDING_ASSIGNED, 0],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, 0],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, 0],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, 0],
        ];
    }
}
