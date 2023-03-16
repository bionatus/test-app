<?php

namespace Tests\Feature\Api\V3\Account\Part\RecentlyViewed;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\Part\RecentlyViewedController;
use App\Http\Resources\Api\V3\Account\Part\RecentlyViewed\BaseResource;
use App\Models\PartDetailCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see RecentlyViewedController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_ACCOUNT_PART_RECENTLY_VIEWED_INDEX;

    /** @test */
    public function an_unauthenticated_user_cannot_proceed()
    {
        $route = URL::route($this->routeName);

        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_returns_a_list_of_part_recently_viewed_by_user()
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        $third  = PartDetailCounter::factory()->usingUser($user)->create(['created_at' => Carbon::now()]);
        $first  = PartDetailCounter::factory()->usingUser($user)->create(['created_at' => Carbon::now()->subDays(2)]);
        $second = PartDetailCounter::factory()->usingUser($user)->create(['created_at' => Carbon::now()->subDay()]);
        PartDetailCounter::factory()
            ->usingUser($otherUser)
            ->usingPart($first->part)
            ->create(['created_at' => Carbon::now()->subDays(2)]);

        $expected = Collection::make([$third->part, $second->part, $first->part]);

        $this->login($user);

        $route    = URL::route($this->routeName);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data->each(function(array $rawPartUser) use ($expected) {
            $partUser = $expected->shift();
            $this->assertSame($partUser->number, $rawPartUser['number']);
        });
    }
}
