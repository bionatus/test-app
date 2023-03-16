<?php

namespace Tests\Feature\Api\V3\Account\Supplier;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Account\SupplierController;
use App\Http\Requests\Api\V3\Account\Supplier\StoreRequest;
use App\Http\Resources\Api\V3\Account\Supplier\BaseResource;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SupplierController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ACCOUNT_SUPPLIER_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $supplier = Supplier::factory()->createQuietly();
        $this->post(URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->getRouteKey()]));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_saves_user_favorite_supplier()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        $this->login($user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'user_id'         => $user->getKey(),
            'supplier_id'     => $supplier->getKey(),
            'visible_by_user' => true,
        ]);
    }

    /** @test */
    public function it_makes_the_supplier_visible_by_user_if_it_was_non_visible()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        $supplierUser = SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->create(["visible_by_user" => false]);

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->getRouteKey()]));
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'id'              => $supplierUser->getKey(),
            'user_id'         => $user->getKey(),
            'supplier_id'     => $supplier->getKey(),
            'visible_by_user' => true,
        ]);
    }

    /** @test */
    public function it_saves_relationships_suppliers_users_without_detaching()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        SupplierUser::factory()->usingUser($user)->count(10)->sequence(fn(Sequence $sequence) => [
            'supplier_id' => Supplier::factory()->createQuietly(),
        ])->create(["visible_by_user" => true]);

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->getRouteKey()]));
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(SupplierUser::class, 11);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'user_id'         => $user->getKey(),
            'supplier_id'     => $supplier->getKey(),
            'visible_by_user' => true,
        ]);
    }

    /** @test */
    public function it_makes_the_supplier_visible_by_user_if_it_was_non_visible_without_detaching()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        SupplierUser::factory()->usingUser($user)->count(10)->sequence(fn(Sequence $sequence) => [
            'supplier_id' => Supplier::factory()->createQuietly(),
        ])->create(["visible_by_user" => true]);

        $supplierUser = SupplierUser::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->create(["visible_by_user" => false]);

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->getRouteKey()]));
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(SupplierUser::class, 11);

        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'id'              => $supplierUser->getKey(),
            'user_id'         => $user->getKey(),
            'supplier_id'     => $supplier->getKey(),
            'visible_by_user' => true,
        ]);
    }
}
