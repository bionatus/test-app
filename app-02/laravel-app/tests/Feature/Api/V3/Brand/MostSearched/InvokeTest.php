<?php

namespace Tests\Feature\Api\V3\Brand\MostSearched;

use App\Constants\RouteNames;
use App\Http\Resources\Models\BrandResource;
use App\Models\Brand;
use App\Models\BrandDetailCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see MostSearchedController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_BRAND_MOST_SEARCHED_INDEX;

    /** @test */
    public function an_unauthenticated_user_cannot_proceed()
    {
        $route = URL::route($this->routeName);

        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_returns_a_list_of_brands_most_searched_sorted_by_most_searched_and_alphabetically()
    {
        $user   = User::factory()->create();
        $brands = Collection::make();

        $brand = Brand::factory()->create();
        $brands->push($brand);
        BrandDetailCounter::factory()->usingBrand($brand)->count(2)->create();

        $brand2 = Brand::factory()->create();
        $brands->push($brand2);
        BrandDetailCounter::factory()->usingBrand($brand2)->count(3)->create();

        $brand3 = Brand::factory()->create(['name' => 'B']);
        $brands->push($brand3);
        BrandDetailCounter::factory()->usingBrand($brand3)->count(4)->create();

        $brand4 = Brand::factory()->create(['name' => 'A']);
        $brands->push($brand4);
        BrandDetailCounter::factory()->usingBrand($brand4)->count(4)->create();

        $this->login($user);

        $route    = URL::route($this->routeName);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BrandResource::jsonSchema(), true), $response);

        $brands = $brands->reverse();
        $data->each(function(array $rawBrandSearch) use ($brands) {
            $brand = $brands->shift();
            $this->assertSame($brand->getRouteKey(), $rawBrandSearch['id']);
        });
    }
}
