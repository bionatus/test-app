<?php

namespace Tests\Feature\Api\V3\Supplier;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\SupplierController;
use App\Http\Resources\Api\V3\Supplier\DetailedResource;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SupplierController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_SUPPLIER_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $supplier = Supplier::factory()->createQuietly();
        $this->withoutExceptionHandling();
        $route = URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->uuid]);
        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_displays_a_supplier()
    {
        $supplier = Supplier::factory()->createQuietly();
        $route    = URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->uuid]);

        $user = User::factory()->create();
        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $supplier->getRouteKey());
    }
}
