<?php

namespace Tests\Feature\Api\V3\Account\Supply\RecentlyViewed;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\Supply\RecentlyAddedController;
use App\Http\Resources\Api\V3\Account\Supply\RecentlyAdded\BaseResource;
use App\Models\CartSupplyCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see RecentlyAddedController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_SUPPLY_RECENTLY_ADDED_INDEX;

    /** @test */
    public function an_unauthenticated_user_cannot_proceed()
    {
        $route = URL::route($this->routeName);

        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_returns_a_list_of_supplies_recently_added_by_user()
    {
        $user       = User::factory()->create();
        $other_user = User::factory()->create();

        $second = CartSupplyCounter::factory()->usingUser($user)->create(['created_at' => Carbon::now()->subDay()]);
        $first  = CartSupplyCounter::factory()->usingUser($user)->create(['created_at' => Carbon::now()]);
        $third  = CartSupplyCounter::factory()->usingUser($user)->create(['created_at' => Carbon::now()->subDays(2)]);
        CartSupplyCounter::factory()->usingUser($other_user)->create(['created_at' => Carbon::now()->subDays(2)]);

        $expected = Collection::make([$first->supply, $second->supply, $third->supply]);

        $this->login($user);

        $route    = URL::route($this->routeName);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data->each(function(array $rawSupplyUser) use ($expected) {
            $supplyUser = $expected->shift();
            $this->assertSame($supplyUser->item->getRouteKey(), $rawSupplyUser['id']);
        });
    }
}
