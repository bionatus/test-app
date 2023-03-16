<?php

namespace Tests\Feature\Api\V4\Supplier\DefaultSupplier;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\Supplier\DefaultSupplierController;
use App\Http\Resources\Api\V4\Supplier\DefaultSupplier\BaseResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see DefaultSupplierController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V4_SUPPLIER_DEFAULT_SHOW;

    /** @test */
    public function an_unauthenticated_user_cannot_proceed()
    {
        $route = URL::route($this->routeName);

        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_returns_default_supplier_by_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->onTheNetwork()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();

        $this->login($user);
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $expectedData = [
            'id'                     => $supplier->getRouteKey(),
            'name'                   => $supplier->name,
            'address'                => $supplier->address,
            'address_2'              => $supplier->address_2,
            'city'                   => $supplier->city,
            'logo'                   => null,
            'image'                  => null,
            'can_use_curri_delivery' => false,
        ];
        $data         = $response->json('data');

        $this->assertSame($expectedData, $data);
    }

    /** @test */
    public function it_returns_the_preferred_supplier_as_default_supplier_by_user()
    {
        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->onTheNetwork()->count(3)->createQuietly();
        $suppliers->each(function(Supplier $supplier) use ($user) {
            SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();
        });
        $preferredSupplier = SupplierUser::factory()->usingUser($user)->usingSupplier(Supplier::factory()
            ->onTheNetwork()
            ->createQuietly())->createQuietly(['preferred' => true])->supplier()->first();
        $this->login($user);
        $response = $this->get(URL::route($this->routeName));
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $expectedData = [
            'id'                     => $preferredSupplier->getRouteKey(),
            'name'                   => $preferredSupplier->name,
            'address'                => $preferredSupplier->address,
            'address_2'              => $preferredSupplier->address_2,
            'city'                   => $preferredSupplier->city,
            'logo'                   => null,
            'image'                  => null,
            'can_use_curri_delivery' => false,
        ];
        $data         = $response->json('data');
        $this->assertSame($expectedData, $data);
    }

    /** @test */
    public function it_returns_the_last_request_ordered_supplier_as_default_supplier_by_user()
    {
        $user            = User::factory()->create();
        $defaultSupplier = Supplier::factory()->onTheNetwork()->createQuietly();
        $suppliers       = Supplier::factory()->onTheNetwork()->count(5)->createQuietly();
        $suppliers->each(function(Supplier $supplier) use ($user) {
            SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();
        });
        Order::factory()->usingUser($user)->usingSupplier($defaultSupplier)->create();
        $this->login($user);
        $response = $this->get(URL::route($this->routeName));
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $expectedData = [
            'id'                     => $defaultSupplier->getRouteKey(),
            'name'                   => $defaultSupplier->name,
            'address'                => $defaultSupplier->address,
            'address_2'              => $defaultSupplier->address_2,
            'city'                   => $defaultSupplier->city,
            'logo'                   => null,
            'image'                  => null,
            'can_use_curri_delivery' => false,

        ];
        $data         = $response->json('data');
        $this->assertSame($expectedData, $data);
    }

    /** @test */
    public function it_returns_the_nearest_related_supplier_as_default_supplier_by_user()
    {
        $user               = User::factory()->create();
        $company            = Company::factory()->create([
            'country'  => 'US',
            'zip_code' => '12345',
        ]);
        $company->latitude  = 0;
        $company->longitude = 0;
        $company->save();
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();
        $suppliers = Supplier::factory()->onTheNetwork()->count(5)->createQuietly([
            'latitude'  => 10,
            'longitude' => 10,
        ]);
        $suppliers->each(function(Supplier $supplier) use ($user) {
            SupplierUser::factory()->usingUser($user)->usingSupplier($supplier)->create();
        });
        $nearestSupplier = Supplier::factory()->onTheNetwork()->createQuietly([
            'latitude'  => 1,
            'longitude' => 1,
        ]);
        SupplierUser::factory()->usingUser($user)->usingSupplier($nearestSupplier)->create();

        $this->login($user);
        $response = $this->get(URL::route($this->routeName));
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $expectedData = [
            'id'                     => $nearestSupplier->getRouteKey(),
            'name'                   => $nearestSupplier->name,
            'address'                => $nearestSupplier->address,
            'address_2'              => $nearestSupplier->address_2,
            'city'                   => $nearestSupplier->city,
            'logo'                   => null,
            'image'                  => null,
            'can_use_curri_delivery' => false,
        ];
        $data         = $response->json('data');
        $this->assertSame($expectedData, $data);
    }

    /** @test */
    public function it_returns_null_no_suppliers_related_by_user()
    {
        $user = User::factory()->create();
        $this->login($user);
        $response = $this->get(URL::route($this->routeName));
        $response->assertStatus(Response::HTTP_OK);
        $data = $response->json('data');
        $this->assertNull($data);
    }
}
