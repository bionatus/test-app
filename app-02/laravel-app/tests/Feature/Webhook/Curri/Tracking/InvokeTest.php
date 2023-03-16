<?php

namespace Tests\Feature\Webhook\Curri\Tracking;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see TrackingController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::WEBHOOK_V1_CURRI_TRACKING_STORE;

    /** @test */
    public function it_updates_the_status_of_a_curri_delivery()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['book_id' => $bookId = 'fake-123']);

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::ID     => $bookId,
            RequestKeys::STATUS => $status = 'delivered',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas(CurriDelivery::tableName(), [
            'book_id' => $bookId,
            'status'  => $status,
        ]);
    }
}
