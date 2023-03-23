<?php

namespace Tests\Feature\Api\V3\Supply;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\SupplyController;
use App\Http\Requests\Api\V3\Supply\IndexRequest;
use App\Http\Resources\Api\V3\Supply\BaseResource;
use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see SupplyController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_SUPPLY_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $route = URL::route($this->routeName);

        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_displays_only_visible_supplies()
    {
        $user            = User::factory()->create();
        $supplyCategory  = SupplyCategory::factory()->create();
        $visibleSupplies = Supply::factory()->usingSupplyCategory($supplyCategory)->visible()->count(7)->create();
        Supply::factory()->usingSupplyCategory($supplyCategory)->nonVisible()->count(5)->create();
        $route = URL::route($this->routeName, [RequestKeys::SUPPLY_CATEGORY => $supplyCategory]);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data        = Collection::make($response->json('data'));
        $dataIds     = $data->pluck('id');
        $expectedIds = $visibleSupplies->pluck('item.uuid');

        $this->assertCount($response->json('meta.total'), $visibleSupplies);
        $this->assertEqualsCanonicalizing($expectedIds, $dataIds);
    }

    /** @test */
    public function it_displays_a_list_of_visible_supplies_sorted_correctly()
    {
        $user             = User::factory()->create();
        $supplyCategory   = SupplyCategory::factory()->create();
        $sorts            = Collection::make([3, 1, 2]);
        $names            = Collection::make([
            3 => ['name C3', 'name A3', 'name B3'],
            1 => ['name A1', 'name B1', 'name C1'],
            2 => ['name B2', 'name C2', 'name A2'],
        ]);
        $expectedSupplies = Collection::make([]);

        $sorts->each(function(string $sort) use ($names, &$expectedSupplies, $supplyCategory) {
            $supplies = Collection::make([]);

            foreach ($names[$sort] as $name) {
                $supply   = Supply::factory()
                    ->usingSupplyCategory($supplyCategory)
                    ->sort($sort)
                    ->visible()
                    ->create(['name' => $name]);
                $supplies = $supplies->push($supply);
            }

            $expectedSupplies = $expectedSupplies->merge($supplies->sortBy('name'));
        });

        $suppliesWithoutSort = Collection::make([]);
        $namesWithoutSort    = Collection::make(['D2', 'D1']);
        $namesWithoutSort->each(function(string $name) use ($supplyCategory, &$suppliesWithoutSort) {
            $supply              = Supply::factory()
                ->usingSupplyCategory($supplyCategory)
                ->visible()
                ->create(['name' => $name]);
            $suppliesWithoutSort = $suppliesWithoutSort->push($supply);
        });
        $suppliesWithoutSortSorted = $suppliesWithoutSort->sortBy('name');

        $expectedSupplies = $expectedSupplies->sortBy('sort')->merge($suppliesWithoutSortSorted);

        $route = URL::route($this->routeName, [RequestKeys::SUPPLY_CATEGORY => $supplyCategory]);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $data        = Collection::make($response->json('data'));
        $dataIds     = $data->pluck('id');
        $expectedIds = $expectedSupplies->pluck('item.uuid');

        $this->assertCount($response->json('meta.total'), $expectedSupplies);
        $this->assertEquals($expectedIds, $dataIds);
    }

    /** @test */
    public function it_filters_supplies_of_a_category_by_search_string()
    {
        $user           = User::factory()->create();
        $supplyCategory = SupplyCategory::factory()->create();
        $expected       = Supply::factory()->usingSupplyCategory($supplyCategory)->visible()->count(3)->sequence(fn(
            Sequence $sequence
        ) => ['name' => 'Fake name' . $sequence->index])->create();
        Supply::factory()->usingSupplyCategory($supplyCategory)->visible()->count(5)->create();

        $route = URL::route($this->routeName, [
            RequestKeys::SUPPLY_CATEGORY => $supplyCategory,
            RequestKeys::SEARCH_STRING   => 'Fake name',
        ]);
        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data        = Collection::make($response->json('data'));
        $dataIds     = $data->pluck('id');
        $expectedIds = $expected->pluck('item.uuid');

        $this->assertCount(3, $data);
        $this->assertEquals($expectedIds, $dataIds);
    }
}
