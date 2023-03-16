<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Actions\Models\Order\ProcessInvoiceOnCanceledOrder as ProcessInvoiceOnCanceledOrderAction;
use App\Events\Order\Canceled;
use App\Listeners\Order\ProcessInvoiceOnCanceledOrder;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\InteractsWithQueue;
use Mockery;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class ProcessInvoiceOnCanceledOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(ProcessInvoiceOnCanceledOrder::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_exportable_trait()
    {
        $this->assertUseTrait(ProcessInvoiceOnCanceledOrder::class, InteractsWithQueue::class);
    }

    /** @test */
    public function it_executes_the_action_process_invoice_on_canceled_order()
    {
        $processInvoiceOnCanceledOrderAction = Mockery::mock(ProcessInvoiceOnCanceledOrderAction::class);
        $processInvoiceOnCanceledOrderAction->shouldReceive('execute')->once()->andReturnNull();
        App::bind(ProcessInvoiceOnCanceledOrderAction::class, fn() => $processInvoiceOnCanceledOrderAction);

        $event = new Canceled(new Order());

        $listener = App::make(ProcessInvoiceOnCanceledOrder::class);
        $listener->handle($event);
    }
}
