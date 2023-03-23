<?php

namespace Tests\Feature\Api\V3\SupportCall;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\SupportCallController;
use App\Http\Requests\Api\V3\SupportCall\StoreRequest;
use App\Http\Resources\Api\V3\SupportCall\BaseResource;
use App\Models\Oem;
use App\Models\SupportCall;
use App\Models\SupportCallCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see SupportCallController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_SUPPORT_CALL_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_stores_a_support_call_for_a_category_without_parent()
    {
        $user     = User::factory()->create();
        $category = SupportCallCategory::factory()->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::CATEGORY => $categorySlug = $category->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(SupportCall::tableName(), [
            'category'    => $categorySlug,
            'subcategory' => null,
            'user_id'     => $user->getKey(),
            'oem_id'      => null,
        ]);
    }

    /** @test */
    public function it_stores_a_support_call_for_a_category_with_parent()
    {
        $user     = User::factory()->create();
        $parent   = SupportCallCategory::factory()->create();
        $category = SupportCallCategory::factory()->usingParent($parent)->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::CATEGORY => $categorySlug = $category->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(SupportCall::tableName(), [
            'category'    => $parent->getRouteKey(),
            'subcategory' => $categorySlug,
            'user_id'     => $user->getKey(),
            'oem_id'      => null,
        ]);
    }

    /** @test */
    public function it_stores_an_oem_type_support_call()
    {
        $user = User::factory()->create();
        $oem  = Oem::factory()->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::CATEGORY => $category = 'oem',
            RequestKeys::OEM      => $oem->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(SupportCall::tableName(), [
            'category'    => $category,
            'subcategory' => null,
            'user_id'     => $user->getKey(),
            'oem_id'      => $oem->getKey(),
        ]);
    }
}
