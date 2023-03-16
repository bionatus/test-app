<?php

namespace Tests\Feature\Api\V3\SupplyCategory\SupplySubcategory;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Resources\Api\V3\SupplyCategory\BaseResource;
use App\Models\SupplyCategory;
use App\Models\SupplyCategoryView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;
use App\Http\Requests\Api\V3\SupplyCategory\SupplySubcategory\IndexRequest;

/** @see SupplySubcategoryController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_SUPPLY_CATEGORY_SUBCATEGORY_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $category = SupplyCategory::factory()->create();

        $route = URL::route($this->routeName, [RouteParameters::SUPPLY_CATEGORY => $category]);

        $this->get($route);
    }
    /** @test */

    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_displays_a_list_of_subcategories_sorted_by_sort_with_null_values_last_and_then_by_name()
    {
        $parent    = SupplyCategory::factory()->visible()->create();
        $category1 = SupplyCategory::factory()->visible()->usingParent($parent)->create([
            'sort' => 5,
            'name' => 'category A',
        ]);
        $category2 = SupplyCategory::factory()->visible()->usingParent($parent)->create([
            'sort' => 1,
            'name' => 'category Z',
        ]);
        $category3 = SupplyCategory::factory()->visible()->usingParent($parent)->create(['name' => 'category C']);
        $category4 = SupplyCategory::factory()->visible()->usingParent($parent)->create([
            'sort' => 3,
            'name' => 'category G',
        ]);
        $category5 = SupplyCategory::factory()->visible()->usingParent($parent)->create([
            'sort' => 3,
            'name' => 'category B',
        ]);
        $category6 = SupplyCategory::factory()->visible()->usingParent($parent)->create(['name' => 'category D']);

        SupplyCategory::factory()->usingParent($parent)->count(3)->create();

        $sortedCategories = Collection::make([$category2, $category5, $category4, $category1, $category3, $category6]);

        $route = URL::route($this->routeName, [RouteParameters::SUPPLY_CATEGORY => $parent]);
        $user  = User::factory()->create();
        $this->login($user);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $sortedCategories);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data->pluck('id'), $sortedCategories->pluck(SupplyCategory::routeKeyName()));
    }

    /** @test */
    public function it_stores_a_supply_category_view_if_page_is_the_first()
    {
        $supplyCategory = SupplyCategory::factory()->visible()->create();
        $route          = URL::route($this->routeName, [
            RouteParameters::SUPPLY_CATEGORY => $supplyCategory,
        ]);

        $user = User::factory()->create();
        $this->login($user);
        $this->get($route);

        $this->assertDatabaseCount(SupplyCategoryView::tableName(), 1);
        $this->assertDatabaseHas(SupplyCategoryView::class, [
            'user_id'            => $user->getKey(),
            'supply_category_id' => $supplyCategory->getKey(),
        ]);
    }

    /** @test */
    public function it_does_not_store_a_supply_category_view_if_page_is_not_the_first()
    {
        $supplyCategory = SupplyCategory::factory()->visible()->create();
        $route          = URL::route($this->routeName, [
            RouteParameters::SUPPLY_CATEGORY => $supplyCategory,
            RouteParameters::PAGE            => 2,
        ]);

        $user = User::factory()->create();
        $this->login($user);
        $this->get($route);

        $this->assertDatabaseCount(SupplyCategoryView::tableName(), 0);
    }

    /** @test */
    public function it_searches_by_search_string_param()
    {
        $supplyCategory = SupplyCategory::factory()->visible()->create();
        $expectedCategories = Collection::make([]);
        $expectedCategories->add(SupplyCategory::factory()->visible()->usingParent($supplyCategory)->create(['name' => 'inside search']));
        $expectedCategories->add(SupplyCategory::factory()->visible()->usingParent($supplyCategory)->create(['name' => 'inside search 2']));
        SupplyCategory::factory()->visible()->usingParent($supplyCategory)->create(['name' => 'outside search']);
        SupplyCategory::factory()->visible()->usingParent($supplyCategory)->create(['name' => 'fake search']);
        $route = URL::route($this->routeName, [
            RouteParameters::SUPPLY_CATEGORY => $supplyCategory,
            RequestKeys::SEARCH_STRING       => 'inside',
        ]);

        $user = User::factory()->create();
        $this->login($user);
        $this->get($route);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertCount($response->json('meta.total'), $expectedCategories);

        $data = $response->json('data');
        foreach($data as $index => $subcategory){
            $this->assertEquals($expectedCategories->get($index)->getRouteKey(), $subcategory['id']);
        }
    }
}
