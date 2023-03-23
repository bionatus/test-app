<?php

namespace Tests\Feature\Api\V3\Account\VerifiedSupplier;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\VerifiedSupplierController;
use App\Http\Resources\Api\V3\Account\VerifiedSupplier\BaseResource;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see VerifiedSupplierController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_VERIFIED_SUPPLIER_COUNT;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_returns_true_if_the_user_has_at_least_a_verified_and_published_and_visible_supplier()
    {
        $user = User::factory()->create();
        SupplierUser::factory()->count(10)->createQuietly();
        $verifiedAndPublishedSupplier = Supplier::factory()->verified()->published()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($verifiedAndPublishedSupplier)->create();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals(true, $data->get('has_verified_suppliers'));
    }

    /** @test */
    public function it_returns_false_if_all_the_suppliers_of_the_user_are_not_verified_or_published()
    {
        $user = User::factory()->create();
        SupplierUser::factory()->count(10)->createQuietly();
        $verifiedSupplier  = Supplier::factory()->verified()->createQuietly();
        $publishedSupplier = Supplier::factory()->published()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($verifiedSupplier)->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($publishedSupplier)->create();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals(false, $data->get('has_verified_suppliers'));
    }

    /** @test */
    public function it_returns_false_if_all_the_suppliers_of_the_user_are_not_visible()
    {
        $user = User::factory()->create();
        SupplierUser::factory()->count(10)->notVisible()->createQuietly();
        $verifiedSupplier  = Supplier::factory()->verified()->createQuietly();
        $publishedSupplier = Supplier::factory()->published()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($verifiedSupplier)->notVisible()->create();
        SupplierUser::factory()->usingUser($user)->usingSupplier($publishedSupplier)->notVisible()->create();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals(false, $data->get('has_verified_suppliers'));
    }
}
