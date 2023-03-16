<?php

namespace Tests\Feature\Api\V4\Supply\Search;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\Supply\SearchController;
use App\Http\Requests\Api\V4\Supply\Search\InvokeRequest;
use App\Http\Resources\Api\V4\Supply\Search\BaseResource;
use App\Models\CartSupplyCounter;
use App\Models\Supply;
use App\Models\SupplySearchCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SearchController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V4_SUPPLY_SEARCH_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_displays_a_supplies_list_filtered_by_name_and_visibility_then_sorted_by_most_added_to_the_cart()
    {
        $supply1 = Supply::factory()->name('Fake cable C')->visible()->create();
        $supply2 = Supply::factory()->name('Fake cable A')->visible()->create();
        $supply3 = Supply::factory()->name('Fake cable B')->create();
        $supply4 = Supply::factory()->name('Fake_cable_1')->visible()->create();
        $supply5 = Supply::factory()->name('Fake_cable_2')->visible()->create();

        CartSupplyCounter::factory()->usingSupply($supply1)->count(1)->create();
        CartSupplyCounter::factory()->usingSupply($supply2)->count(3)->create();
        CartSupplyCounter::factory()->usingSupply($supply3)->count(2)->create();

        $expectedSupplies = Collection::make([
            $supply2,
            $supply1,
            $supply4,
            $supply5,
        ]);

        Supply::factory()->count(2)->create();

        $search = 'Cable';
        $route  = URL::route($this->routeName, [RequestKeys::NAME => $search]);
        $user   = User::factory()->create();

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawSupply, int $index) use ($expectedSupplies) {
            $supply = $expectedSupplies->get($index);
            $this->assertSame($supply->item->getRouteKey(), $rawSupply['id']);
        });
    }

    /** @test */
    public function it_stores_a_part_search_log()
    {
        $searchName = 'Cable';

        Supply::factory()->name('Fake cable C')->visible()->create();
        Supply::factory()->name('Fake cable A')->visible()->create();
        Supply::factory()->count(10)->create();

        $route = URL::route($this->routeName, [RequestKeys::NAME => $searchName]);

        $this->login($user = User::factory()->create());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount(SupplySearchCounter::tableName(), 1);
        $this->assertDatabaseHas(SupplySearchCounter::tableName(), [
            'user_id'  => $user->getKey(),
            'criteria' => $searchName,
            'results'  => 2,
        ]);
    }
}
