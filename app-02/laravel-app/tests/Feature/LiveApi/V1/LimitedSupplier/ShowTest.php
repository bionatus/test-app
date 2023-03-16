<?php

namespace Tests\Feature\LiveApi\V1\LimitedSupplier;

use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\LimitedSupplierController;
use App\Http\Resources\LiveApi\V1\LimitedSupplier\BaseResource;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see LimitedSupplierController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_LIMITED_SUPPLIER_SHOW;

    /** @test */
    public function it_display_a_limited_supplier()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $route    = URL::route($this->routeName, $supplier);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::JsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $supplier->getRouteKey());
    }
}
