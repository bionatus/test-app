<?php

namespace Tests\Feature\LiveApi\V2\Order\Invoice;

use App\Constants\MediaCollectionNames;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\InvoiceController;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderStaff;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see InvoiceController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_INVOICE_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->createQuietly()->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:read,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_deletes_the_order_invoice()
    {
        $staff    = Staff::factory()->createQuietly(['name' => 'Fake name']);
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();
        OrderStaff::factory()->usingStaff($staff)->usingOrder($order)->create();
        OrderDelivery::factory()->usingOrder($order)->create();

        $file    = UploadedFile::fake()->create('new-invoice.pdf', 1024);
        $order->addMedia($file)->preservingOriginal()->toMediaCollection(MediaCollectionNames::INVOICE);

        $this->assertNotNull($order->getFirstMedia(MediaCollectionNames::INVOICE));

        Auth::shouldUse('live');
        $this->login($staff);
        $route    = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertFalse($order->refresh()->hasMedia(MediaCollectionNames::INVOICE));
        $this->assertCount(0, $order->getMedia(MediaCollectionNames::INVOICE));
    }
}
