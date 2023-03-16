<?php

namespace Tests\Feature\Api\V4\SupplyCategory;

use App\Constants\RouteNames;
use App\Http\Resources\Api\V4\SupplyCategory\BaseResource;
use App\Models\SupplyCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see SupplyCategoryController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_SUPPLY_CATEGORY_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_categories_sorted_by_sort_with_null_values_last_and_then_by_name()
    {
        $route = URL::route($this->routeName);
        $user  = User::factory()->create();
        $this->login($user);

        $category1 = SupplyCategory::factory()->visible()->create(['sort' => 5, 'name' => 'category A']);
        $category2 = SupplyCategory::factory()->visible()->create(['sort' => 1, 'name' => 'category Z']);
        $category3 = SupplyCategory::factory()->visible()->create(['name' => 'category C']);
        $category4 = SupplyCategory::factory()->visible()->create(['sort' => 3, 'name' => 'category G']);
        $category5 = SupplyCategory::factory()->visible()->create(['sort' => 3, 'name' => 'category B']);
        $category6 = SupplyCategory::factory()->visible()->create(['name' => 'category D']);

        SupplyCategory::factory()->count(3)->create();

        $sortedCategories = Collection::make([$category2, $category5, $category4, $category1, $category3, $category6]);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $sortedCategories);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data->pluck('id'), $sortedCategories->pluck(SupplyCategory::routeKeyName()));
    }
}
