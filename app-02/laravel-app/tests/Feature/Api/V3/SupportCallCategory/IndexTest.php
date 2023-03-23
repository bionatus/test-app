<?php

namespace Tests\Feature\Api\V3\SupportCallCategory;

use App\Constants\RouteNames;
use App\Http\Resources\Api\V3\SupportCallCategory\BaseResource;
use App\Models\SupportCallCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see SupportCallCategoryController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_SUPPORT_CALL_CATEGORY_INDEX;

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
        $category1 = SupportCallCategory::factory()->create(['sort' => 5, 'name' => 'category A']);
        $category2 = SupportCallCategory::factory()->create(['sort' => 1, 'name' => 'category Z']);
        $category3 = SupportCallCategory::factory()->create(['name' => 'category C']);
        $category4 = SupportCallCategory::factory()->create(['sort' => 3, 'name' => 'category G']);
        $category5 = SupportCallCategory::factory()->create(['sort' => 3, 'name' => 'category B']);
        $category6 = SupportCallCategory::factory()->create(['name' => 'category D']);
        SupportCallCategory::factory()->usingParent($category1)->create(['name' => 'subcategory A1']);
        SupportCallCategory::factory()->usingParent($category6)->create(['name' => 'subcategory D1']);

        $sortedCategories = Collection::make([$category2, $category5, $category4, $category1, $category3, $category6]);

        $this->login(User::factory()->create());
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $sortedCategories);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($sortedCategories->pluck(SupportCallCategory::routeKeyName()), $data->pluck('id'));
    }
}
