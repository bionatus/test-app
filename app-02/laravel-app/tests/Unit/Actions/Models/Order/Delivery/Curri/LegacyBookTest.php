<?php

namespace Tests\Unit\Actions\Models\Order\Delivery\Curri;

use App;
use App\Actions\Models\Order\Delivery\Curri\LegacyBook;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Curri\Curri;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class LegacyBookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_books_a_curri_delivery_and_updates_the_order_information()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $user          = User::factory()->create();
        $order         = Order::factory()->usingSupplier($supplier)->usingUser($user)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $curriDelivery = CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => $bookId = 'book id',
            'price'       => $fee = 1200,
            'tracking_id' => $trackingId = 'tracking id',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $book = new LegacyBook($order);
        $book->execute();
        $order->refresh();
        $curriDelivery->refresh();

        $this->assertSame($bookId, $curriDelivery->book_id);
        $this->assertDatabaseHas(CurriDelivery::tableName(), [
            'id'          => $curriDelivery->getKey(),
            'book_id'     => $bookId,
            'tracking_id' => $trackingId,
        ]);
        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'fee' => $fee,
        ]);
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_COMPLETED_DONE,
        ]);
    }

    /** @test */
    public function it_returns_an_exception_on_curri_client_error()
    {
        $this->expectException(Exception::class);

        $supplier      = Supplier::factory()->createQuietly();
        $user          = User::factory()->create();
        $order         = Order::factory()->usingSupplier($supplier)->usingUser($user)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'status'  => 'error',
            'message' => 'error message',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $book = new LegacyBook($order);
        $book->execute();
    }
}
