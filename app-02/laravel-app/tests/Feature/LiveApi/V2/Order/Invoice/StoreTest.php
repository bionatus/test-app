<?php

namespace Tests\Feature\LiveApi\V2\Order\Invoice;

use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\InvoiceController;
use App\Http\Requests\LiveApi\V2\Order\Invoice\StoreRequest;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use App\Models\OrderDelivery;
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
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_INVOICE_STORE;

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
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_uploads_an_invoice_to_the_order()
    {
        $file = UploadedFile::fake()->create('invoice.pdf', 1024);

        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();
        OrderDelivery::factory()->usingOrder($order)->create();

        $this->assertNull($order->getFirstMedia(MediaCollectionNames::INVOICE));

        Auth::shouldUse('live');
        $this->login($staff);
        $route    = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);
        $response = $this->post($route, [RequestKeys::FILE => $file]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);

        $this->assertTrue($order->refresh()->hasMedia(MediaCollectionNames::INVOICE));
        $this->assertCount(1, $order->getMedia(MediaCollectionNames::INVOICE));
    }

    /** @test */
    public function it_replaces_an_invoice_to_the_order()
    {
        $file = UploadedFile::fake()->create('new-invoice.pdf', 1024);

        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();
        OrderDelivery::factory()->usingOrder($order)->create();

        $oldFile = $order->addMedia($file)
            ->preservingOriginal()
            ->usingName('old_invoice.pdf')
            ->toMediaCollection(MediaCollectionNames::INVOICE);

        Auth::shouldUse('live');
        $this->login($staff);
        $route    = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);
        $response = $this->post($route, [RequestKeys::FILE => $file]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);

        $this->assertDeleted($oldFile);
        $media = $order->getFirstMedia(MediaCollectionNames::INVOICE);
        $this->assertSame($file->getClientOriginalName(), $media->file_name);
    }
}
